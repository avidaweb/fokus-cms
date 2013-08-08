<?php
$template = array(
    'name' => 'Three Colors',
    'files' => array(
    ),
    'mobile' => array(
    ),
    'newsletter' => array(
    ),
    'slots' => array(
        'footer' => array(
            'name' => 'Fußleiste'
        ),
        'sidebar-a' => array(
            'name' => 'Seitenleiste A'
        ),
        'sidebar-b' => array(
            'name' => 'Seitenleiste B'
        ),
        'links' => array(
            'name' => 'Link-Galerie',
            'dclass' => 'three-colors-links.php'
        ),
        'big' => array(
            'name' => 'Haupt-Teaser',
            'dclass' => 'three-colors-big.php'
        )
    ),
    'menus' => array(
        'area-a' => array(
            'name' => 'Bereich A'
        ),
        'area-b' => array(
            'name' => 'Bereich B'
        ),
        'area-c' => array(
            'name' => 'Bereich C'
        )
    ),
    'custom_fields' => array(
        'color' => array(
            'name' => 'Hintergrundfarbe',
            'type' => 'select',
            'global' => true,
            'values' => array(
                '' => 'Standard',
                '026181' => 'blau',
                '008151' => 'grün',
                '820455' => 'lila'
            )
        ),
        'slot-links' => array(
            'name' => 'Slot "Link-Galerie" anzeigen?',
            'global' => true,
            'type' => 'checkbox'
        ),
        'slot-big' => array(
            'name' => 'Slot "Haupt-Teaser" anzeigen?',
            'global' => true,
            'type' => 'checkbox'
        )
    ),
    'global_custom_fields' => array(
    ),
    'classes' => array(
        'border_under_document' => array(
            'name' => 'Trennlinie unter Dokument anzeigen',
            'restriction' => 'document'
        )
    )
);
?>