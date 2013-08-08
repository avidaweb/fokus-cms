<!DOCTYPE html> 
<html lang="<?php $fks->writeLanguage(true); ?>"> 
<head> 
    <?php
    $api->addCssFiles(array(
        $fks->getTemplateDir().'css/jquery.bxslider.css',
        $fks->getTemplateDir().'css/colorbox.css',
        $fks->getTemplateDir().'css/fokus.css',
        $fks->getTemplateDir().'css/style.css',
        $fks->getTemplateDir().'css/handheld.css'
    ));

    $api->addJsFile($fks->getTemplateDir().'js/jquery.bxslider.min.js', 'bxslider', 49);
    $api->addJsFile($fks->getTemplateDir().'js/jquery.colorbox-min.js', 'colorbox', 49);
    $api->addJsFile($fks->getTemplateDir().'js/script.js', 'script.js', 50);

    $fks->writeHeader(array(
        'html5' => true
    ));

    $content->setForm(array(
        'view' => 'flat'
    ));

    $content->setGallery(array(
        'img_width' => 185,
        'img_height' => 140,
        'link' => 'all'
    ));

    $storage = $api->getStorageBase('three-colors');
    ?>

    <?php
    if(strlen($fks->getCustomField('color')) == 6)
        $color = $fks->getCustomField('color');
    elseif($api->getStorage('color', 'three-colors'))
        $color = $api->getStorage('color', 'three-colors');
    else
        $color = '026181';
    ?>

    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="<?php echo ($storage['logo']?$api->getImageUrl($storage['logo'], 64, 64):''); ?>" type="image/x-icon" />
</head>

<body class="body color-<?php echo $color; ?>">
    <div class="topbar">
        <div class="wrapper">
            <?php
            $fks->writeGhostLink('Direktbearbeitung aktivieren');
            $fks->writeSearchForm(array('input_placeholder' => 'Suchbegriff eingeben...'));
            $navigation->writeLanguageSwitcher(false, 24, true);
            ?>
        </div>
    </div>

    <div class="main">
        <div class="wrapper">
            <header>
                <a class="logo" href="<?php $fks->writeRoot(); ?>">
                    <?php
                    $static_logo = ''; // path to static logo file

                    if($static_logo)
                        echo '<img src="'.$static_logo.'" alt=" " width="259" />';
                    elseif($storage['logo'])
                        echo '<img src="'.$api->getImageUrl($storage['logo'], 259, 0).'" alt=" " width="259" />';
                    else
                        echo '<span>'.str_replace(array('http://', 'https://', 'www.'), '', DOMAIN).'</span>';
                    ?>
                </a>

                <a class="shownav" href="#">
                    <span class="a"></span>
                    <span class="b"></span>
                    <span class="c"></span>
                </a>

                <nav class="area area-a">
                    <?php
                    echo '<h6>'.$storage['area'][$fks->getLanguage(true)]['a'].'</h6>';
                    $navigation->write(array(
                        'menu' => 'area-a',
                        'html_tag' => 'nav'
                    ));
                    ?>
                </nav>
                <nav class="area area-b">
                    <?php
                    echo '<h6>'.$storage['area'][$fks->getLanguage(true)]['b'].'</h6>';
                    $navigation->write(array(
                        'menu' => 'area-b',
                        'html_tag' => 'nav'
                    ));
                    ?>
                </nav>
                <nav class="area area-c">
                    <?php
                    echo '<h6>'.$storage['area'][$fks->getLanguage(true)]['c'].'</h6>';
                    $navigation->write(array(
                        'menu' => 'area-c',
                        'html_tag' => 'nav'
                    ));
                    ?>
                </nav>

                <span class="clr"></span>
            </header>

            <section>

                <aside>
                    <?php
                    $content->write(array(
                        'responsive' => true,
                        'slot' => 'sidebar-a',
                        'class' => 'sidebar sidebar-a'.($fks->getCustomField('slot-big')?' sidebar-a-border':''),
                        'column_padding' => array(0)
                    ));

                    $content->write(array(
                        'responsive' => true,
                        'slot' => 'sidebar-b',
                        'class' => 'sidebar sidebar-b',
                        'column_padding' => array(0)
                    ));
                    ?>
                </aside>

                <?php
                if($fks->getCustomField('slot-big') && !$fks->isSearch())
                {
                    echo '<div class="slot-big">';
                    $content->write(array(
                        'responsive' => true,
                        'slot' => 'big',
                        'class' => 'slot-big-slider',
                        'column_padding' => array(0),
                        'no_column_wrapper' => true,
                        'no_document_wrapper' => true
                    ));
                    echo '</div>';
                }

                if($fks->getCustomField('slot-links') && !$fks->isSearch())
                {
                    $content->write(array(
                        'responsive' => true,
                        'slot' => 'links',
                        'class' => 'slot-links',
                        'column_padding' => array(0),
                        'no_column_wrapper' => true,
                        'no_document_wrapper' => true
                    ));
                }
                ?>

                <article>
                    <?php
                    $content->write(array(
                        'responsive' => true
                    ));
                    ?>
                </article>

                <?php
                $content->write(array(
                    'slot' => 'footer',
                    'id' => 'contentfooter',
                    'responsive' => true
                ))
                ?>

                <span class="clr"></span>
            </section>
        </div>
    </div>

    <footer>
        <div class="wrapper">
            <div class="area mrgn"></div>

            <nav class="area area-a">
                <?php
                echo '<h6>'.$storage['area'][$fks->getLanguage(true)]['a'].'</h6>';
                $navigation->write(array(
                    'menu' => 'area-a',
                    'html_tag' => 'nav'
                ));
                ?>
            </nav>
            <nav class="area area-b">
                <?php
                echo '<h6>'.$storage['area'][$fks->getLanguage(true)]['b'].'</h6>';
                $navigation->write(array(
                    'menu' => 'area-b',
                    'html_tag' => 'nav'
                ));
                ?>
            </nav>
            <nav class="area area-c">
                <?php
                echo '<h6>'.$storage['area'][$fks->getLanguage(true)]['c'].'</h6>';
                $navigation->write(array(
                    'menu' => 'area-c',
                    'html_tag' => 'nav'
                ));
                ?>
            </nav>

            <span class="clr"></span>
        </div>
    </footer>

    <div class="bg-layer"></div>

    <?php
    $fks->writeFooter();
    ?>
</body>
</html>