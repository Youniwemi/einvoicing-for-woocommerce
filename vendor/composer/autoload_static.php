<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8ab2a06a262afea251affce2d379cbf5
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'setasign\\Fpdi\\' => 14,
        ),
        'U' => 
        array (
            'UXML\\' => 5,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Svg\\' => 4,
            'Sabberworm\\CSS\\' => 15,
        ),
        'P' => 
        array (
            'Psr\\Cache\\' => 10,
            'PHPStan\\PhpDocParser\\' => 21,
        ),
        'M' => 
        array (
            'Metadata\\' => 9,
            'Masterminds\\' => 12,
        ),
        'J' => 
        array (
            'JMS\\Serializer\\' => 15,
        ),
        'F' => 
        array (
            'FontLib\\' => 8,
        ),
        'E' => 
        array (
            'Einvoicing\\' => 11,
            'Easybill\\ZUGFeRD\\' => 17,
            'Easybill\\ZUGFeRD211\\' => 20,
        ),
        'D' => 
        array (
            'Dompdf\\' => 7,
            'Doctrine\\Instantiator\\' => 22,
            'Doctrine\\Common\\Lexer\\' => 22,
            'Doctrine\\Common\\Annotations\\' => 28,
            'DigitalInvoice\\' => 15,
        ),
        'A' => 
        array (
            'Atgp\\FacturX\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'setasign\\Fpdi\\' => 
        array (
            0 => __DIR__ . '/..' . '/setasign/fpdi/src',
        ),
        'UXML\\' => 
        array (
            0 => __DIR__ . '/..' . '/josemmo/uxml/src',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Svg\\' => 
        array (
            0 => __DIR__ . '/..' . '/phenx/php-svg-lib/src/Svg',
        ),
        'Sabberworm\\CSS\\' => 
        array (
            0 => __DIR__ . '/..' . '/sabberworm/php-css-parser/src',
        ),
        'Psr\\Cache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/cache/src',
        ),
        'PHPStan\\PhpDocParser\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpstan/phpdoc-parser/src',
        ),
        'Metadata\\' => 
        array (
            0 => __DIR__ . '/..' . '/jms/metadata/src',
        ),
        'Masterminds\\' => 
        array (
            0 => __DIR__ . '/..' . '/masterminds/html5/src',
        ),
        'JMS\\Serializer\\' => 
        array (
            0 => __DIR__ . '/..' . '/jms/serializer/src',
        ),
        'FontLib\\' => 
        array (
            0 => __DIR__ . '/..' . '/phenx/php-font-lib/src/FontLib',
        ),
        'Einvoicing\\' => 
        array (
            0 => __DIR__ . '/..' . '/josemmo/einvoicing/src',
        ),
        'Easybill\\ZUGFeRD\\' => 
        array (
            0 => __DIR__ . '/..' . '/easybill/zugferd-php/src/zugferd10',
        ),
        'Easybill\\ZUGFeRD211\\' => 
        array (
            0 => __DIR__ . '/..' . '/easybill/zugferd-php/src/zugferd211',
        ),
        'Dompdf\\' => 
        array (
            0 => __DIR__ . '/..' . '/dompdf/dompdf/src',
        ),
        'Doctrine\\Instantiator\\' => 
        array (
            0 => __DIR__ . '/..' . '/doctrine/instantiator/src/Doctrine/Instantiator',
        ),
        'Doctrine\\Common\\Lexer\\' => 
        array (
            0 => __DIR__ . '/..' . '/doctrine/lexer/src',
        ),
        'Doctrine\\Common\\Annotations\\' => 
        array (
            0 => __DIR__ . '/..' . '/doctrine/annotations/lib/Doctrine/Common/Annotations',
        ),
        'DigitalInvoice\\' => 
        array (
            0 => __DIR__ . '/..' . '/youniwemi/digital-invoice/src',
        ),
        'Atgp\\FacturX\\' => 
        array (
            0 => __DIR__ . '/..' . '/atgp/factur-x/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'Smalot\\PdfParser\\' => 
            array (
                0 => __DIR__ . '/..' . '/smalot/pdfparser/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Dompdf\\Cpdf' => __DIR__ . '/..' . '/dompdf/dompdf/lib/Cpdf.php',
        'FPDF' => __DIR__ . '/..' . '/setasign/fpdf/fpdf.php',
        'Milo\\Schematron' => __DIR__ . '/..' . '/milo/schematron/src/Schematron.php',
        'Milo\\SchematronException' => __DIR__ . '/..' . '/milo/schematron/src/Schematron.php',
        'Milo\\SchematronHelpers' => __DIR__ . '/..' . '/milo/schematron/src/Schematron.php',
        'Milo\\SchematronXPath' => __DIR__ . '/..' . '/milo/schematron/src/Schematron.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8ab2a06a262afea251affce2d379cbf5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8ab2a06a262afea251affce2d379cbf5::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit8ab2a06a262afea251affce2d379cbf5::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit8ab2a06a262afea251affce2d379cbf5::$classMap;

        }, null, ClassLoader::class);
    }
}
