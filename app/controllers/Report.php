<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Report extends Controller
{
    const MAX_PHOTO_BYTES = 5242880; // 5 MB
    const ALLOWED_PHOTO_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    /** Resident: list own reports (FR6, status tracking). */
    public function mine()
    {
        $user = eco_require_role(['resident']);
        $this->call->model('Report_model');
        $this->call->view('resident/reports', [
            'title'   => 'My reports',
            'reports' => $this->Report_model->mine($user['user_id']),
        ]);
    }

    /** Resident: show the dumping report form. */
    public function create()
    {
        eco_require_role(['resident']);
        $this->render_form([], []);
    }

    /** Resident: validate and save a dumping report with its photo. */
    public function store()
    {
        $user   = eco_require_role(['resident']);
        $desc   = trim($_POST['description'] ?? '');
        $loc    = trim($_POST['location'] ?? '');
        $lat    = trim($_POST['latitude'] ?? '');
        $lng    = trim($_POST['longitude'] ?? '');
        $errors = [];

        if ($desc === '') {
            $errors[] = 'Description is required.';
        } elseif (mb_strlen($desc) > 1000) {
            $errors[] = 'Description must be 1000 characters or fewer.';
        }
        if ($loc === '') {
            $errors[] = 'Location is required (for example, the street or a landmark).';
        } elseif (mb_strlen($loc) > 255) {
            $errors[] = 'Location must be 255 characters or fewer.';
        }
        if ($lat !== '' && (!is_numeric($lat) || abs((float) $lat) > 90)) {
            $errors[] = 'Latitude is invalid.';
        }
        if ($lng !== '' && (!is_numeric($lng) || abs((float) $lng) > 180)) {
            $errors[] = 'Longitude is invalid.';
        }

        $photo_url = null;
        $file = $_FILES['photo'] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'A photo is required as evidence.';
        } else {
            $photo_url = $this->save_photo($file, $errors);
        }

        if ($errors) {
            $this->render_form($errors, [
                'description' => $desc, 'location' => $loc, 'latitude' => $lat, 'longitude' => $lng,
            ]);
            return;
        }

        $this->call->model('Report_model');
        $this->Report_model->create_report([
            'resident_id'   => $user['user_id'],
            'description'   => $desc,
            'location_text' => $loc,
            'latitude'      => $lat !== '' ? $lat : null,
            'longitude'     => $lng !== '' ? $lng : null,
            'photo_url'     => $photo_url,
            'status'        => 'Open',
        ]);

        eco_flash('success', 'Report submitted. Thank you for helping keep the community clean.');
        eco_go('resident/reports');
    }

    /** Admin: the dumping ticket queue, optionally filtered by status. */
    public function index()
    {
        eco_require_role(['admin']);
        $this->call->model('Report_model');
        $status = $_GET['status'] ?? '';
        if ($status !== '' && !in_array($status, Report_model::$statuses, true)) {
            $status = '';
        }
        $this->call->view('admin/tickets/index', [
            'title'    => 'Dumping tickets',
            'reports'  => $this->Report_model->all_filtered($status),
            'filter'   => $status,
            'statuses' => Report_model::$statuses,
        ]);
    }

    /** Admin: one ticket with its photo and status controls. */
    public function showTicket($id)
    {
        eco_require_role(['admin']);
        $this->call->model('Report_model');
        $report = $this->Report_model->find($id);
        if (!$report) show_404();
        $this->call->view('admin/tickets/show', [
            'title'    => 'Ticket #' . (int) $report['report_id'],
            'report'   => $report,
            'statuses' => Report_model::$statuses,
        ]);
    }

    /** Admin: move a ticket to a new status and tell the resident (queued notification). */
    public function updateTicket($id)
    {
        $admin = eco_require_role(['admin']);
        $this->call->model('Report_model');
        $this->call->model('Notification_model');
        $report = $this->Report_model->find($id);
        if (!$report) show_404();

        $status = $_POST['status'] ?? '';
        if (!in_array($status, Report_model::$statuses, true)) {
            eco_flash('error', 'Choose a valid status.');
            eco_go('admin/tickets/' . (int) $report['report_id']);
        }
        if ($status === $report['status']) {
            eco_flash('info', 'The ticket already has that status.');
            eco_go('admin/tickets/' . (int) $report['report_id']);
        }

        $this->Report_model->update_status($report['report_id'], $status, $admin['user_id']);
        $this->Notification_model->queue(
            $report['resident_id'], 'Email',
            'Your dumping report #' . (int) $report['report_id'] . ' is now: ' . $status . '.'
        );
        eco_audit('Set ticket to ' . $status, 'dumping_reports', $report['report_id']);
        eco_flash('success', 'Ticket updated to ' . $status . '. The resident has been notified.');
        eco_go('admin/tickets/' . (int) $report['report_id']);
    }

    /** Admin: download every report as a CSV file. */
    public function exportCsv()
    {
        eco_require_role(['admin']);
        $this->call->model('Report_model');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="ecobin-reports-' . date('Ymd-His') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads accents correctly
        fputcsv($out, ['Report #', 'Filed', 'Resident', 'Email', 'Location', 'Latitude', 'Longitude', 'Status', 'Assigned to', 'Description', 'Photo']);
        foreach ($this->Report_model->everything() as $r) {
            fputcsv($out, [
                $r['report_id'], $r['created_at'], $r['resident_name'], $r['resident_email'],
                $r['location_text'], $r['latitude'], $r['longitude'], $r['status'],
                $r['assignee_name'], $r['description'], $r['photo_url'] ? eco_url($r['photo_url']) : '',
            ]);
        }
        fclose($out);
        exit;
    }

    /** Admin: download a PDF summary of all reports (FPDF). */
    public function exportPdf()
    {
        eco_require_role(['admin']);
        $this->call->model('Report_model');
        require_once APP_DIR . 'libraries/fpdf/fpdf.php';

        $rows = $this->Report_model->everything();
        $counts = ['Open' => 0, 'In Progress' => 0, 'Resolved' => 0];
        foreach ($rows as $r) {
            if (isset($counts[$r['status']])) $counts[$r['status']]++;
        }
        $latin = function ($s) { return mb_convert_encoding((string) $s, 'ISO-8859-1', 'UTF-8'); };
        $clip  = function ($s, $n) use ($latin) {
            $s = (string) $s;
            return $latin(mb_strlen($s) > $n ? mb_substr($s, 0, $n - 3) . '...' : $s);
        };

        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(31, 78, 61);
        $pdf->Cell(0, 9, $latin('Eco-Bin: Illegal Dumping Reports'), 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(90, 90, 90);
        $pdf->Cell(0, 6, $latin('Generated ' . date('F j, Y g:i A') . '   |   Total: ' . count($rows)
            . '   Open: ' . $counts['Open'] . '   In progress: ' . $counts['In Progress'] . '   Resolved: ' . $counts['Resolved']), 0, 1);
        $pdf->Ln(3);

        $widths = [14, 36, 36, 48, 25, 30, 75];
        $heads  = ['#', 'Filed', 'Resident', 'Location', 'Status', 'Assigned', 'Description'];
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(234, 243, 238);
        $pdf->SetTextColor(31, 78, 61);
        foreach ($heads as $i => $h) $pdf->Cell($widths[$i], 7, $h, 1, 0, 'L', true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(30, 30, 30);
        foreach ($rows as $r) {
            if ($pdf->GetY() > 185) {
                $pdf->AddPage();
                $pdf->SetFont('Arial', '', 8);
            }
            $cells = [
                $r['report_id'],
                substr($r['created_at'], 0, 16),
                $clip($r['resident_name'], 24),
                $clip($r['location_text'], 30),
                $r['status'],
                $clip($r['assignee_name'] ?: '-', 22),
                $clip($r['description'], 60),
            ];
            foreach ($cells as $i => $c) {
                $pdf->Cell($widths[$i], 6, $latin($c), 1, 0, 'L');
            }
            $pdf->Ln();
        }
        if (!$rows) {
            $pdf->Cell(0, 8, $latin('No reports yet.'), 0, 1);
        }

        $pdf->Output('D', 'ecobin-reports-' . date('Ymd-His') . '.pdf');
        exit;
    }

    /* ---------- helpers ---------- */

    private function render_form(array $errors, array $values)
    {
        $this->call->view('resident/report_form', [
            'title'  => 'Report illegal dumping',
            'errors' => $errors,
            'values' => $values,
        ]);
    }

    /** Check and store an uploaded photo. Returns the public relative path, or NULL on error. */
    private function save_photo(array $file, array &$errors)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'The photo could not be uploaded. Try a smaller image.';
            return null;
        }
        if ($file['size'] > self::MAX_PHOTO_BYTES) {
            $errors[] = 'The photo must be 5 MB or smaller.';
            return null;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::ALLOWED_PHOTO_TYPES[$mime]) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'The photo must be a JPG, PNG, or WEBP image.';
            return null;
        }

        $dir = ROOT_DIR . 'public/uploads/reports/';
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            $errors[] = 'The upload folder could not be created. Check folder permissions.';
            return null;
        }

        $name = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_PHOTO_TYPES[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            $errors[] = 'The photo could not be saved. Check folder permissions.';
            return null;
        }
        return 'uploads/reports/' . $name;
    }
}
