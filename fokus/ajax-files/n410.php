<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');

if(!$user->r('dat', 'new') || ($index != 'n410' && $index != 'n420'))
    exit('no rights');
    
$upload_size = $base->getUploadSizeLimit();
$max_megapixel = round(($upload_size * $upload_size / 1000 / 1000), 1);
$upload_time = $base->getUploadTimeLimit();

$dir = $fksdb->save($_REQUEST['dir'], 1);
$limit = $fksdb->save($_REQUEST['limit'], 1);
$is_images = $fksdb->save($_REQUEST['images']);
$parent = $fksdb->save($_REQUEST['parent']);

function ordner_struktur($fksdb, $parent, $output)
{         
    if($parent != 0)
    {
        $ergebnis = $fksdb->query("SELECT titel, id, dir FROM ".SQLPRE."files WHERE isdir = '1' AND id = '".$parent."' LIMIT 1");
        while($row = $fksdb->fetch($ergebnis))
        {
            $output = '/'.(!$parent == $row->id?'<strong>':'').$row->titel.(!$parent == $row->id?'</strong>':'').$output;
            
            if($row->dir)
                $output = ordner_struktur($fksdb, $row->dir, $output);
        }
    }
    
    return $output;
}
$dirs = '/'.(!$dir?'<strong>':'').$trans->__('Hauptverzeichnis').(!$dir?'</strong>/':'').ordner_struktur($fksdb, $dir, '');

echo '
<h1>
    '.($is_images?
        (!$parent?
            ($limit != 1?
                $trans->__('Bilder hochladen.')
                :
                $trans->__('Bild hochladen.')
            )
            :
            $trans->__('Neues Version eines Bildes hochladen.')
        )
        :
        (!$parent?
            ($limit != 1?
                $trans->__('Dateien hochladen.')
                :
                $trans->__('Datei hochladen.')
            )
            :
            $trans->__('Neue Version einer Datei hochladen.')
        )
    ).'
</h1>

<div class="box">';
?>

    <form id="fileupload" class="<?php echo ($is_images?'is_images':'is_files'); ?>" method="POST" enctype="multipart/form-data" data-max-upload="<?php echo round(($base->getUploadLimit() * 1048500), 0); ?>">
    
        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
        <div class="row fileupload-buttonbar">
            <div class="span7">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button rbutton goaway shortcut-new">
                    <?php
                    if($limit == 1)
                        echo $trans->__('Datei hinzufügen');
                    else
                        echo $trans->__('Dateien hinzufügen');
                    ?>
                    <input type="file" name="files[]"<?php echo ($limit == 1?'':' multiple'); ?>>
                </span>
                <button type="submit" class="btn btn-primary start rbutton">
                    <i class="icon-upload icon-white"></i>
                    <span>Uploadvorgang starten</span>
                </button>
                <button type="reset" class="btn btn-warning cancel rbutton">
                    <i class="icon-ban-circle icon-white"></i>
                    <span>Uploadvorgang abbrechen</span>
                </button>
                <button type="button" class="btn btn-danger delete rbutton">
                    <i class="icon-trash icon-white"></i>
                    <span>Dateien aus Warteschleife entfernen</span>
                </button>
            </div>
            <!-- The global progress information -->
            <div class="span5 fileupload-progress fade">
                <h2 class="calibri"><?php $trans->__('Upload läuft.', true); ?></h2>
                
                <div class="upload_dir">
                    <?php echo $trans->__('Upload in Verzeichnis: %1', false, array($dirs)); ?>
                </div>
                <!-- The extended global progress information -->
                <div class="progress-extended">&nbsp;</div>
                <!-- The global progress bar -->
                <div class="progress ui-progressbar ui-widget ui-widget-content ui-corner-all" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="bar ui-progressbar-value ui-widget-header ui-corner-left ui-corner-right" style="width:0%;"></div>
                </div>
            </div>
        </div>
        <!-- The loading indicator is shown during file processing -->
        <div class="fileupload-loading"></div>
        
        <!-- The table listing the files available for upload/download -->
        <table role="presentation" class="table table-striped"><tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody></table>
    </form>
    
    <!-- modal-gallery is the modal dialog used for the image gallery -->
    <div id="modal-gallery" class="modal modal-gallery hide fade" data-filter=":odd">
        <div class="modal-header">
            <a class="close" data-dismiss="modal">&times;</a>
            <h3 class="modal-title"></h3>
        </div>
        <div class="modal-body"><div class="modal-image"></div></div>
        <div class="modal-footer">
            <a class="btn modal-download" target="_blank">
                <i class="icon-download"></i>
                <span>Download</span>
            </a>
            <a class="btn btn-success modal-play modal-slideshow" data-slideshow="5000">
                <i class="icon-play icon-white"></i>
                <span>Slideshow</span>
            </a>
            <a class="btn btn-info modal-prev">
                <i class="icon-arrow-left icon-white"></i>
                <span>Previous</span>
            </a>
            <a class="btn btn-primary modal-next">
                <span>Next</span>
                <i class="icon-arrow-right icon-white"></i>
            </a>
        </div>
    </div>
    
    <!-- The template to display files available for upload -->
    <script id="template-upload" type="text/x-tmpl">
    {% for (var i=0, file; file=o.files[i]; i++) { %}
        <tr class="template-upload fade" data-name="{%=file.name%}">
            <td class="preview"><span class="fade"></span></td>
            <td class="name"><span>{%=file.name%}</span></td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            {% if (file.error) { %}
                <td class="error" colspan="2">
                    <?php $trans->__('Beim Upload dieser Datei ist leider ein Fehler aufgetreten.', true); ?>
                </td>
            {% } else if (o.files.valid && !i) { %}
                <td>
                    <div class="progress ui-progressbar ui-widget ui-widget-content ui-corner-all" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                        <div class="bar ui-progressbar-value ui-widget-header ui-corner-left ui-corner-right" style="width:0%;"></div>
                    </div>
                    <div class="upload-successed">
                        <?php $trans->__('erfolgreich hochgeladen', true); ?>
                    </div>
                </td>
                
                <td class="edit">
                    {% if (!i) { %}
                        <a><?php $trans->__('bearbeiten', true); ?></a>
                    {% } %}
                </td>
            {% } else { %}
                <td colspan="2"></td>
            {% } %}
        </tr>
    {% } %}
    </script>
    <!-- The template to display files available for download -->
    <script id="template-download" type="text/x-tmpl">
    {% for (var i=0, file; file=o.files[i]; i++) { %}
        {% if (file.error) { %}
        <tr class="template-download fade has_error">
        {% } else { %}
        <tr class="template-download fade">
        {% } %}
            {% if (file.error) { %}
                <td></td>
                <td class="name"><span>{%=file.name%}</span></td>
                <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
                <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
            {% } else { %}
                <td class="preview">{% if (file.thumbnail_url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
                {% } %}</td>
                <td class="name">
                    <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a>
                </td>
                <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
                <td colspan="2"></td>
            {% } %}
        </tr>
    {% } %}
    </script>
    
    <?php
    echo '
    <ul class="upload_info">
        <li>'.$trans->__('max. %1 MB', false, array(round($base->getUploadLimit(), 1))).' </li>
        '.($is_images?
        '<li>'.$trans->__('max. %1 Megapixel (z.B. %2 x %2 Pixel)', false, array($max_megapixel, $upload_size)).'</li>
        <li>'.$trans->__('JPG, PNG und GIF').'</li>
        ':'
        <li>
            '.($upload_time >= 120?
                $trans->__('max. %1 Minuten Upload-Dauer', false, array(round($upload_time / 60, 0)))
                :
                ($upload_time?
                    $trans->__('max. %1 Sekunden Upload-Dauer', false, array($upload_time))
                    :
                    $trans->__('keine Beschränkung der Upload-Dauer')
                )
            ).'
        </li>
        <li>'.$trans->__('alle Dateiformate erlaubt').'</li>
        ').'
        '.($limit != 1?'<li>'.$trans->__('Multi-Upload möglich').'</li>':'').'
    </ul>
</div>

<div class="box_save" style="display:block;">
    <input type="button" value="'.$trans->__('schließen').'" class="bs1" /> 
</div>';
?>