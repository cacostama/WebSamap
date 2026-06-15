<?php
/**
 * seo.php — Meta tags reutilizables (description, keywords, Open Graph,
 * Twitter Card y datos estructurados MedicalOrganization).
 *
 * Antes de incluirlo, definir en la página:
 *   $seoTitle    string  título para OG/Twitter (sin "SAMAP - ").
 *   $seoDesc     string  descripción única de la página (~150 caracteres).
 *   $seoKeywords string  (opcional) palabras clave separadas por coma.
 *   $seoImage    string  (opcional) ruta relativa de imagen para compartir.
 *
 * Requiere $URL (definido en funciones/db.php).
 */

$seoTitle    = isset($seoTitle) && $seoTitle !== '' ? $seoTitle : 'SAMAP — Medicina Prepaga';
$seoDesc     = isset($seoDesc) && $seoDesc !== '' ? $seoDesc : 'SAMAP, medicina prepaga del Sanatorio Adventista de Asunción. Más de 35 años cuidando la salud de las familias paraguayas.';
$seoKeywords = isset($seoKeywords) && $seoKeywords !== '' ? $seoKeywords : 'medicina prepaga, salud Paraguay, SAMAP, Sanatorio Adventista, seguro médico, planes de salud';
$seoImg      = isset($seoImage) && $seoImage !== '' ? $seoImage : 'assets/images/logo.png';

$baseUrl  = (isset($URL) ? $URL : '/');
$pageUrl  = $baseUrl . ltrim($_SERVER['REQUEST_URI'] ?? '', '/');
$imageUrl = (strpos($seoImg, 'http') === 0 || strpos($seoImg, '//') === 0)
    ? $seoImg
    : $baseUrl . ltrim($seoImg, '/');

$descAttr  = htmlspecialchars($seoDesc, ENT_QUOTES, 'UTF-8');
$titleAttr = htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8');
$kwAttr    = htmlspecialchars($seoKeywords, ENT_QUOTES, 'UTF-8');
?>
    <meta name="description" content="<?php echo $descAttr; ?>">
    <meta name="keywords" content="<?php echo $kwAttr; ?>">
    <meta name="author" content="SAMAP S.A.">
    <link rel="canonical" href="<?php echo htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8'); ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="SAMAP">
    <meta property="og:locale" content="es_PY">
    <meta property="og:title" content="<?php echo $titleAttr; ?>">
    <meta property="og:description" content="<?php echo $descAttr; ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $titleAttr; ?>">
    <meta name="twitter:description" content="<?php echo $descAttr; ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>">

    <!-- Datos estructurados -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "MedicalOrganization",
      "name": "SAMAP — Medicina Prepaga",
      "alternateName": "Sanatorio Adventista de Asunción Medicina Prepaga",
      "url": "<?php echo $baseUrl; ?>",
      "logo": "<?php echo $baseUrl; ?>assets/images/logo.png",
      "description": "<?php echo $descAttr; ?>",
      "areaServed": "PY",
      "medicalSpecialty": "Multiple",
      "address": {
        "@type": "PostalAddress",
        "addressCountry": "PY",
        "addressLocality": "Asunción"
      }
    }
    </script>
