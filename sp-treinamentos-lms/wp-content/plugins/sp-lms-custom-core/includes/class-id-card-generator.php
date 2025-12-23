<?php

if (!defined('ABSPATH')) {
    exit;
}

class SP_LMS_ID_Card_Generator
{

    public function __construct()
    {
        add_action('init', array($this, 'register_endpoint'));
        add_action('template_redirect', array($this, 'generate_card'));
    }

    public function register_endpoint()
    {
        add_rewrite_endpoint('carteirinha', EP_ROOT);
    }

    public function generate_card()
    {
        global $wp_query;

        // URL format: /carteirinha/COURSE_ID
        if (!isset($wp_query->query_vars['carteirinha'])) {
            return;
        }

        $course_id = intval($wp_query->query_vars['carteirinha']);
        $user_id = get_current_user_id();

        if (!$user_id) {
            wp_redirect(wp_login_url());
            exit;
        }

        // Verify completion
        $is_completed = tutor_utils()->is_completed_course($course_id, $user_id);
        if (!$is_completed) {
            wp_die('Você ainda não concluiu este curso para emitir a carteirinha.', 'Acesso Negado', array('response' => 403));
        }

        $this->render_pdf($user_id, $course_id);
        exit;
    }

    private function render_pdf($user_id, $course_id)
    {
        // Require FPDF (assuming it's in a lib folder or helper)
        if (!class_exists('FPDF')) {
            // In a real scenario, we'd include the library here.
            // require_once SP_LMS_CORE_PATH . 'lib/fpdf.php';
            wp_die('Biblioteca de PDF não instalada.');
        }

        $user_info = get_userdata($user_id);
        $course_title = get_the_title($course_id);
        $cpf = get_user_meta($user_id, 'cpf', true); // Required by spec

        // Mock PDF generation
        $pdf = new FPDF('L', 'mm', array(85, 55)); // Credit Card size
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Carteirinha de Estudante', 0, 1, 'C');

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 5, 'Nome: ' . $user_info->display_name, 0, 1);
        $pdf->Cell(0, 5, 'CPF: ' . $cpf, 0, 1);
        $pdf->Cell(0, 5, 'Curso: ' . $course_title, 0, 1);
        $pdf->Cell(0, 5, 'Validade: Indeterminada', 0, 1);

        // Validation QR Code (Mock)
        $validation_url = home_url('/validar-carteirinha?code=' . md5($user_id . $course_id));
        $pdf->Cell(0, 5, 'Validar: ' . $validation_url, 0, 1);

        $pdf->Output('D', 'carteirinha.pdf');
    }
}
