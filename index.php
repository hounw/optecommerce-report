<?php
// Leer el archivo json
$jsonFile = 'results.json';
if (!file_exists($jsonFile)) {
    die("Error: No se encontró el archivo $jsonFile.");
}

$jsonData = file_get_contents($jsonFile);
$data = json_decode($jsonData, true);

if (!$data) {
    die("Error: El archivo JSON no es válido.");
}

$dominio = $data['Dominio'] ?? 'Desconocido';
$fecha = $data['Fecha'] ?? date('Y-m-d H:i:s');
$resultados = $data['Resultados'] ?? [];
$recomendaciones = $data['Recomendaciones'] ?? [];

$totalPeso = 0;
$totalPuntaje = 0;

foreach ($resultados as $res) {
    $totalPeso += $res['Peso'] ?? 0;
    $totalPuntaje += $res['Puntaje'] ?? 0;
}

$scorePercentage = $totalPeso > 0 ? ($totalPuntaje / $totalPeso) * 100 : 0;
$scorePercentageFormat = number_format($scorePercentage, 2);

function getGrade($score)
{
    if ($score >= 90)
        return 'A';
    if ($score >= 80)
        return 'B';
    if ($score >= 70)
        return 'C';
    if ($score >= 60)
        return 'D';
    return 'F';
}

function getGradeColor($grade)
{
    switch ($grade) {
        case 'A':
            return '#4CAF50'; // Green
        case 'B':
            return '#8BC34A'; // Light Green
        case 'C':
            return '#FFC107'; // Yellow
        case 'D':
            return '#FF9800'; // Orange
        case 'F':
            return '#F44336'; // Red
        default:
            return '#757575'; // Grey
    }
}

$overallGrade = getGrade($scorePercentage);
$overallGradeColor = getGradeColor($overallGrade);

// Grouping
$groupedResults = [];
foreach ($resultados as $res) {
    $cat = $res['Categoría'] ?? 'Sin Categoría';
    $sec = $res['Sección'] ?? 'Sin Sección';
    if (!isset($groupedResults[$cat])) {
        $groupedResults[$cat] = [];
    }
    if (!isset($groupedResults[$cat][$sec])) {
        $groupedResults[$cat][$sec] = [];
    }
    $groupedResults[$cat][$sec][] = $res;
}

function calculateCategoryScore($sections)
{
    $peso = 0;
    $puntaje = 0;
    foreach ($sections as $secName => $items) {
        foreach ($items as $i) {
            $peso += $i['Peso'] ?? 0;
            $puntaje += $i['Puntaje'] ?? 0;
        }
    }
    return $peso > 0 ? ($puntaje / $peso) * 100 : 0;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Auditoría Web - Opt-ecommerce by Webifica</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #f53d39;
            --gradient: linear-gradient(135deg, #ff6800, #ec1b65);
            --bg-light: #f9f9fb;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --white: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 12px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
            padding-bottom: 4rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Top Branding */
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .brand-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Main Header Score */
        .hero {
            background: var(--white);
            border-radius: var(--radius);
            padding: 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-lg);
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--gradient);
        }

        .hero-left h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .hero-left p {
            font-size: 1.1rem;
            color: var(--text-muted);
        }

        .hero-right {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .grade-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 4rem;
            font-weight: 800;
            color: var(--white);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 0.75rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .score-percentage {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-dark);
            background: var(--bg-light);
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            border: 1px solid var(--border-color);
        }

        /* Sections */
        .section-title {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-dark);
        }

        /* Accordion for Categories */
        .category-group {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .category-header {
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            background: var(--white);
            transition: background 0.2s;
            user-select: none;
        }

        .category-header:hover {
            background: #fdfdfd;
        }

        .category-title {
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .category-score {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .grade-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 700;
            color: white;
            font-size: 0.95rem;
            min-width: 40px;
            display: inline-block;
            text-align: center;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .toggle-icon {
            font-size: 1.2rem;
            color: var(--text-muted);
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--bg-light);
        }

        .category-content {
            display: none;
            padding: 0 2rem 2rem 2rem;
            border-top: 1px solid var(--border-color);
            background: #fafafa;
        }

        .category-group.active .category-content {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .category-group.active .toggle-icon {
            transform: rotate(180deg);
            background: var(--primary);
            color: white;
        }

        /* Sub-sections */
        .subsection {
            margin-top: 2rem;
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.02);
        }

        .subsection:first-child {
            margin-top: 1.5rem;
        }

        .subsection-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--bg-light);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem 0.5rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table th {
            text-transform: uppercase;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.75rem;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover td {
            background-color: #fdfdfd;
        }

        .data-table td.col-grade {
            width: 100px;
            text-align: center;
        }

        .data-table td.col-score {
            width: 100px;
            text-align: right;
            font-weight: 600;
        }

        .priority-badge {
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .priority-alta {
            background: #fee2e2;
            color: #b91c1c;
        }

        .priority-media {
            background: #fef3c7;
            color: #b45309;
        }

        .priority-baja {
            background: #ecfdf5;
            color: #047857;
        }

        /* Marketing Banner */
        .cta-banner {
            margin-top: 4rem;
            background: var(--gradient);
            border-radius: var(--radius);
            padding: 3.5rem 2rem;
            text-align: center;
            color: white;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .cta-banner::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 60%);
            pointer-events: none;
        }

        .cta-banner h2 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .cta-banner p {
            font-size: 1.15rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            z-index: 1;
        }

        .btn {
            background: white;
            color: var(--primary);
            padding: 1.2rem 2.5rem;
            border-radius: 50px;
            font-weight: 800;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 1;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
            color: #ec1b65;
        }

        /* Table container for recommendations */
        .rec-container {
            padding: 2rem;
        }

        @media (max-width: 768px) {
            .hero {
                flex-direction: column;
                text-align: center;
                gap: 2.5rem;
                padding: 2rem;
            }

            .hero-right {
                align-items: center;
            }

            .category-header {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }

            .data-table,
            .data-table tbody,
            .data-table tr,
            .data-table td {
                display: block;
                width: 100%;
            }

            .data-table thead {
                display: none;
            }

            .data-table tr {
                margin-bottom: 1rem;
                border: 1px solid var(--border-color);
                border-radius: 8px;
                padding: 0.5rem;
            }

            .data-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
                border-bottom: none;
            }

            .data-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 1rem;
                width: 45%;
                text-align: left;
                font-weight: 700;
                font-size: 0.8rem;
                text-transform: uppercase;
                color: var(--text-muted);
            }

            .rec-container {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="brand-header">
            <div class="brand-logo">🏷️ Opt-ecommerce by Webifica</div>
            <div class="brand-subtitle">Auditoría UX/CRO Pro</div>
        </div>

        <!-- Header / Hero -->
        <div class="hero">
            <div class="hero-left">
                <h1>
                    <?php echo htmlspecialchars($dominio); ?>
                </h1>
                <p>📊 Resultados del análisis experto en conversión y experiencia de usuario</p>
            </div>
            <div class="hero-right">
                <div class="grade-circle" style="background-color: <?php echo $overallGradeColor; ?>;">
                    <?php echo $overallGrade; ?>
                </div>
                <div class="score-percentage">
                    Puntaje Global:
                    <?php echo $scorePercentageFormat; ?>%
                </div>
            </div>
        </div>

        <!-- Resultados -->
        <h2 class="section-title">🔍 Detalles de la Auditoría</h2>

        <div class="categories-wrapper">
            <?php foreach ($groupedResults as $catName => $sections):
    $catScore = calculateCategoryScore($sections);
    $catGrade = getGrade($catScore);
    $catColor = getGradeColor($catGrade);
?>
            <div class="category-group">
                <div class="category-header" onclick="this.parentElement.classList.toggle('active')">
                    <div class="category-title">
                        📁
                        <?php echo htmlspecialchars($catName); ?>
                    </div>
                    <div class="category-score">
                        <span style="font-weight: 700; font-size: 1.1rem; color: var(--text-dark);">
                            <?php echo number_format($catScore, 2); ?>%
                        </span>
                        <span class="grade-badge" style="background-color: <?php echo $catColor; ?>;">
                            <?php echo $catGrade; ?>
                        </span>
                        <span class="toggle-icon">▼</span>
                    </div>
                </div>
                <div class="category-content">
                    <?php foreach ($sections as $secName => $items): ?>
                    <div class="subsection">
                        <h3 class="subsection-title">📑
                            <?php echo htmlspecialchars($secName); ?>
                        </h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Elemento Evaluado</th>
                                    <th class="col-grade">Grado</th>
                                    <th class="col-score">Puntaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item):
            $notaStr = str_replace('%', '', $item['Nota']);
            $itemScore = floatval($notaStr);
            $itemGrade = getGrade($itemScore);
            $itemColor = getGradeColor($itemGrade);
?>
                                <tr>
                                    <td data-label="Elemento Evaluado" style="font-weight: 500; font-size: 0.95rem;">
                                        <?php echo htmlspecialchars($item['Elemento']); ?>
                                    </td>
                                    <td data-label="Grado" class="col-grade">
                                        <span class="grade-badge" style="background-color: <?php echo $itemColor; ?>;">
                                            <?php echo $itemGrade; ?>
                                        </span>
                                    </td>
                                    <td data-label="Puntaje" class="col-score">
                                        <?php echo htmlspecialchars($item['Nota']); ?>
                                    </td>
                                </tr>
                                <?php
        endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
    endforeach; ?>
                </div>
            </div>
            <?php
endforeach; ?>
        </div>

        <!-- Recomendaciones -->
        <?php if (!empty($recomendaciones)): ?>
        <h2 class="section-title" style="margin-top: 4rem;">💡 Recomendaciones y Plan de Acción</h2>
        <div class="category-group" style="display: block;">
            <div class="rec-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Prioridad</th>
                            <th>Recomendación</th>
                            <th>Categoría / Sección</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recomendaciones as $rec):
        $prio = strtolower($rec['Prioridad'] ?? '');
        $prioClass = 'priority-media';
        if (strpos($prio, 'alta') !== false)
            $prioClass = 'priority-alta';
        elseif (strpos($prio, 'baja') !== false)
            $prioClass = 'priority-baja';
?>
                        <tr>
                            <td data-label="Prioridad" style="vertical-align: top; width: 120px;">
                                <span class="priority-badge <?php echo $prioClass; ?>">
                                    <?php echo htmlspecialchars($rec['Prioridad'] ?? 'Media'); ?>
                                </span>
                            </td>
                            <td data-label="Recomendación"
                                style="font-weight: 500; font-size: 1rem; line-height: 1.5; padding-right: 2rem;">
                                <?php echo htmlspecialchars($rec['Recomendación']); ?>
                            </td>
                            <td data-label="Contexto" style="vertical-align: top; width: 250px;">
                                <div style="font-weight: 600; color: var(--text-dark); margin-bottom: 0.25rem;">
                                    <?php echo htmlspecialchars($rec['Categoría']); ?>
                                </div>
                                <div style="color: var(--text-muted); font-size: 0.85rem;">
                                    ↳
                                    <?php echo htmlspecialchars($rec['Sección']); ?>
                                </div>
                            </td>
                        </tr>
                        <?php
    endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
endif; ?>

        <!-- CTA Marketing -->
        <div class="cta-banner">
            <h2>🚀 ¿Listo para escalar tu eCommerce?</h2>
            <p>Implementa estas mejoras y transforma tu tráfico en ventas tangibles. Impulsa tu facturación de
                inmediato.</p>
            <a href="#" class="btn">Solicitar Asistencia Técnica</a>
        </div>

    </div>

</body>

</html>