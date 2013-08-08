<?php
$dclass = array(
    'name' => 'Link-Galerie',
    'content' => '
        <a href=":urlpicker(name=URL;):" title=":input(name=Link-Titel;input_width=300;):" class="link-gallery-item">
            :img(name=Grafik;no_wrapper=true;width=136;height=136;dimension=pixel;align=block;):
        </a>
    '
);
?>