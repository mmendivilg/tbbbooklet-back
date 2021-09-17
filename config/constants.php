<?php
return [
    'empresa' => [
        'logotipo' => [
            'ruta' => storage_path( 'app/empresa/logotipo/' ),
        ]
    ],
    // 'plantilla' => [
    //     'finanza' => [
    //         'gasto' => [
    //             'formato' => [
    //                 'pdf' => 'pdf',
    //             ],
    //             'ruta' => [
    //                 'pdf' => storage_path( 'app/plantillas/pdf/gasto/%name%' ),
    //             ],
    //         ]
    //     ]
    // ],
    'ghostscript' => [ //para merge-pdf
        'plantilla' => [
            'pdf' => [
                'ruta' => storage_path( 'app/plantillas/Ghostscript/pdf_info.ps' ),
            ]
        ]
    ],
    'css' => [
        'mpdf-bootstrap' => storage_path( 'app/plantillas/css/mpdf-bootstrap.css' ),
    ],
];
