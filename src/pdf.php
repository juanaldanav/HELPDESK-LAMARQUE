<?php
// Hoja de servicio en PDF usando TCPDF (reutilizado del vendor de GLPI, sin instalar nada).
// Devuelve la ruta del archivo generado o null si la librería no está disponible.

function service_sheet_pdf(array $t, array $thread, array $assets): ?string
{
    $tcpdf = '/var/www/glpi/vendor/tecnickcom/tcpdf/tcpdf.php';
    if (!is_file($tcpdf)) { error_log('[soporte-v2 pdf] TCPDF no encontrado'); return null; }
    require_once $tcpdf;

    $dir = rtrim(cfg('uploads_dir'), '/\\') . '/pdf';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $path = $dir . '/hoja-servicio-' . (int)$t['id'] . '.pdf';

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
    $pdf->SetCreator('Soporte Lamarque');
    $pdf->SetTitle('Hoja de servicio · Ticket #' . (int)$t['id']);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(16, 14, 16);
    $pdf->SetAutoPageBreak(true, 18);
    $pdf->AddPage();

    $brand = '#006970';
    $esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $logo = __DIR__ . '/../public/assets/lamarque-logo.png';

    // Membrete: logo (Image absoluta) a la izquierda, datos del documento a la derecha
    if (is_file($logo)) {
        // 323x80 → 42mm de ancho ≈ 10.4mm de alto
        $pdf->Image($logo, 16, 15, 42, 0, 'PNG', '', 'T', false, 300);
    }
    $html = '<table cellpadding="2"><tr>'
        . '<td width="50%">&nbsp;</td>'
        . '<td width="50%" align="right"><span style="font-size:8px;color:#5d6f70;">HOJA DE MANTENIMIENTO / SERVICIO</span><br/>'
        . '<b style="font-size:14px;color:' . $brand . ';">Ticket #' . (int)$t['id'] . '</b><br/>'
        . '<span style="font-size:9px;color:#5d6f70;">' . $esc($t['entity_name']) . '</span></td>'
        . '</tr></table>'
        . '<table cellpadding="0"><tr><td style="border-bottom:1.4px solid ' . $brand . ';"></td></tr></table><br/>';

    // Metadatos
    $rows = [
        ['Asunto', $t['name']],
        ['Categoría', $t['cat_name'] ?: 'General'],
        ['Estado', status_label((int)$t['status'])],
        ['Urgencia', urgency_label((int)$t['urgency'])],
        ['Técnico', $t['tecnico_name'] ?: '—'],
        ['Apertura', fecha($t['date'])],
    ];
    if (!empty($t['closedate']) && $t['closedate'] !== '0000-00-00 00:00:00') $rows[] = ['Cierre', fecha($t['closedate'])];
    foreach ($assets as $a) $rows[] = ['Equipo', $a['name'] . ' (' . $a['serial'] . ') · ' . $a['type_label']];

    $html .= '<table cellpadding="4" style="font-size:10px;">';
    foreach ($rows as [$k, $v]) {
        $html .= '<tr><td width="25%" style="color:#5d6f70;"><b>' . $esc($k) . '</b></td><td width="75%">' . $esc($v) . '</td></tr>';
    }
    $html .= '</table><br/>';

    // Bitácora
    $html .= '<p style="font-size:11px;color:' . $brand . ';"><b>BITÁCORA</b></p>';
    foreach ($thread as $m) {
        $content = preg_replace('~\[foto:[^\]]+\]~', '[foto adjunta]', (string)$m['content']);
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        $content = strip_tags(preg_replace('~<br\s*/?>|</p>~i', "\n", $content));
        // Glifos sin soporte en la fuente del PDF → ASCII
        $content = strtr($content, ['☑' => '[X]', '☐' => '[  ]', '🧾' => '', '✓' => '[X]']);
        $html .= '<p style="font-size:9px;color:#5d6f70;"><b>' . $esc($m['author']) . '</b> · ' . $esc(fecha($m['date'])) . '</p>'
            . '<p style="font-size:10px;">' . nl2br($esc(trim($content))) . '</p>';
    }

    $pdf->writeHTML($html, true, false, true, false, '');

    // Separación amplia antes de las firmas (salto a nueva zona si queda poco espacio)
    if ($pdf->GetY() > 235) $pdf->AddPage();
    else $pdf->Ln(26);

    $firmas = '<table cellpadding="6" style="font-size:9px;color:#5d6f70;">'
        . '<tr>'
        . '<td width="45%" align="center" style="border-top:0.6px solid #90a0a0;">Firma del técnico</td>'
        . '<td width="10%"></td>'
        . '<td width="45%" align="center" style="border-top:0.6px solid #90a0a0;">Firma de la sucursal</td>'
        . '</tr></table>';
    $pdf->writeHTML($firmas, true, false, true, false, '');
    $pdf->Output($path, 'F');
    return is_file($path) ? $path : null;
}
