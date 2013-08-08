<?php
$dclass = array(
    'name' => 'Blogartikel',
    'content' => '
        <div class="blog-article">
            :h1(name=Überschrift;):
            :h3(name=Einleitungstext;class=intro;):

            <figure>
                :img(name=Hauptbild;width=290;height=150;dimension=pixel;no_wrapper=true;align=block;):
                <figcaption>:text(name=Bildunterschrift;):</figcaption>
            </figure>

            :content(name=Inhalt;):
        </div>
    '
);
?>