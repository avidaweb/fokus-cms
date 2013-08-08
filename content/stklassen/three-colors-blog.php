<?php
$teaser = array(
    'name' => 'Blogartikel-Teaser',
    'content' => '
        <div class="blog-teaser">
            :img(name=Hauptbild;width=200;height=140;dimension=pixel;no_wrapper=true;link=true;align=left;):
            :h2(name=Überschrift;):
            :p(name=Einleitungstext;limit_chars=310;more= ...mehr lesen;):
        </div>
    '
);
?>