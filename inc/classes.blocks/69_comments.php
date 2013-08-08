<?php
class Block_69 extends BlockBasic
{
    private $options = array(), $comments = array(), $event = '';
    private $html_event = '', $html_form = '', $html_comments = '';

    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $event = '';
        $form = '';

        $c = self::$base->fixedUnserialize($this->html);
        if(!is_array($c)) $c = array();
        $c = (object)$c;

        if(count(self::$fks->getCommentErrors()))
        {
            if(self::$fks->getCommentErrors('fks_form_id') == $this->block->vid)
            {
                $this->event = 'error';

                self::$fks->deleteCommentError('fks_form_id');
                
                $event = '<ul class="fks_form_error">';
                foreach(self::$fks->getCommentErrors() as $e)
                {
                    $event .= '<li>'.$e.'</li>';
                }
                $event .= '</ul>';
            } 
        }
        elseif($_GET['comment_ok'])
        {
            $this->event = 'success';

            $event = '<div class="fks_form_ok">'.self::$trans->__('Kommentar erfolgreich abgeschickt').'</div>';
        }
           
        // Formular zum Eintragen
        if(($c->name || $c->email || $c->web || $c->text) && !$c->hide)
        {     
            $pc = new stdClass();
            if($_POST['fks_comment_send'])
            {
                $v = self::$base->vars('POST');
                $pc = (object)$v;
            }
            
            if(!$c->loggedusers)
            {
                if(!$pc->name)
                    $pc->name = trim(self::$api->getUserData('first_name').' '.self::$api->getUserData('last_name'));
                if(!$pc->email)
                    $pc->email = self::$api->getUserData('email');
            }
            
            $form = '
            <form action="'.self::$fks->getURL().'#fks_comment_container_'.$this->block->vid.'" method="post" class="fks_comment_form">
            '.($c->name?'
                <p class="fks_comment_name">
                    <label for="cfn_'.$this->block->vid.'">Name:</label>
                    <input type="text" name="name" value="'.$pc->name.'" id="cfn_'.$this->block->vid.'"'.(self::$fks->isHTML5() && $c->name_p?' required':'').(!$c->loggedusers && $c->loggedusers_force && $pc->name && self::$api->isLogged()?' readonly':'').' />
                </p>
            ':'').'
            '.($c->email?'
                <p class="fks_comment_email">
                    <label for="cfe_'.$this->block->vid.'">Email:</label>
                    <input type="'.(self::$fks->isHTML5()?'email':'text').'" name="email" value="'.$pc->email.'" id="cfe_'.$this->block->vid.'"'.(self::$fks->isHTML5() && $c->email_p?' required':'').(!$c->loggedusers && $c->loggedusers_force && $pc->email && self::$api->isLogged()?' readonly':'').' />
                </p>
            ':'').'
            '.($c->web?'
                <p class="fks_comment_web">
                    <label for="cfw_'.$this->block->vid.'">Webseite:</label>
                    <input type="'.(self::$fks->isHTML5()?'url':'text').'" name="web" value="'.$pc->web.'" id="cfw_'.$this->block->vid.'"'.(self::$fks->isHTML5() && $c->web_p?' required':'').' />
                </p>
            ':'').'
            '.($c->text?'
                <p class="fks_comment_text">
                    <label for="cft_'.$this->block->vid.'">Kommentar:</label>
                    <textarea name="text" id="cft_'.$this->block->vid.'"'.(self::$fks->isHTML5() && $c->text_p?' required':'').'>'.$pc->text.'</textarea>
                </p>
            ':'').'
            <p class="fks_comment_submit">
                <input type="submit" class="submit" value="Kommentieren" name="fks_comment_go" />
                
                <input type="hidden" name="fks_comment_id" value="'.$this->block->vid.'" />
                <input type="hidden" name="fks_comment_send" value="'.time().'" />
                <input type="hidden" name="fks_comment_document" value="'.$this->block->dokument.'" />
                '.self::$api->getHashInput().'
                
                <input type="text" style="visibility:hidden; width:2px; height:2px;" name="fks_url" value="" class="fks_check" />
            </p>
            </form>';
        }
        
        $comment_opt = array(
            'use_name' => ($c->name?true:false),
            'use_email' => ($c->email?true:false),
            'use_web' => ($c->web?true:false),
            'use_comment' => ($c->text?true:false),
            'hide_name' => ($c->name_h?true:false),
            'hide_email' => ($c->email_h?true:false),
            'hide_web' => ($c->web_h?true:false),
            'hide_comment' => ($c->text_h?true:false),
            'web_nofollow' => (!$c->web_df?true:false),
            'insert_userdata' => (!$c->loggedusers?true:false),
            'lock_userdata' => (!$c->loggedusers && $c->loggedusers_force?true:false)
        );
        $this->options = $comment_opt;
        
        
        $kk = self::$fksdb->query("SELECT * FROM ".SQLPRE."comments WHERE vid = '".$this->block->vid."' AND ".($c->type == 0?"element = '".self::$fks->getElementID()."' ".(self::$fks->isDclass()?" AND dk = '".self::$fks->getDclassDocumentID()."'":" AND dk = '0'"):"dokument = '".$this->block->dokument."'")." ORDER BY id ".($c->chrono?"DESC":"ASC")); 
        
        $comments = (self::$fksdb->count($kk)?'<div class="fks_comments">':'');
        
        while($k = self::$fksdb->fetch($kk))
        {
            $awaiting_moderation = false;
            if(!$k->frei)
            {
                if(($k->ip && !$k->benutzer && $k->ip == self::$api->getVisitorIP() && $k->timestamp > time() - 86400) || ($k->benutzer && $k->benutzer == self::$user->getID()))
                    $awaiting_moderation = true;
                else
                    continue;
            }
            
            $name = '';
            if($k->name && $c->name && !$c->name_h)
                $name = ($k->web && $c->web && !$c->web_h?'<a href="'.$k->web.'" target="_blank"'.(!$c->web_df?' rel="nofollow"':'').'>'.$k->name.'</a>':$k->name);
                
            if(!$k->name && $c->name)
                $name = 'Anonym';
            
            $c_text = '';
            if($k->text && $c->text && !$c->text_h)
                $c_text = nl2br($k->text);   
                
            $the_comment = '
            <div class="fks_comment">
                <p class="fks_comment_head">
                    '.($name?'<span class="fks_comment_head_name">'.$name.'</span>':'').'
                    '.($k->email && $c->email && !$c->email_h?'<span class="fks_comment_head_email">('.$k->email.')</span>':'').'
                    <span class="fks_comment_head_date">am '.date('d.m.Y', $k->timestamp).' um '.date('H:i', $k->timestamp).' Uhr</span>
                </p>
                '.($c_text?'
                <p class="fks_comment_body">
                    '.$c_text.'
                </p>
                ':'').'
                '.($awaiting_moderation?'
                <p class="fks_comment_awaiting_moderation">
                    '.self::$trans->__('Dieser Kommentar wartet auf Freischaltung').'
                </p>
                ':'').'
            </div>';
            
            // Hook: after_comment
            $hatr = array(
                'user' => $k->benutzer,
                'ip' => $k->ip,
                'open' => ($k->frei?true:false),
                'name' => $name, 
                'real_name' => $k->name,
                'email' => $k->email, 
                'comment' => $c_text, 
                'real_comment' => $k->text, 
                'web' => $k->web,
                'timestamp' => $k->timestamp,
                'opt' => $comment_opt
            );

            $the_comment = self::$api->execute_filter('after_comment', $the_comment, $hatr);
            $the_comment .= self::$api->execute_hook('after_comment', $hatr, true);

            unset($hatr['opt']);
            $this->comments[] = $hatr;

            unset($hatr);
            
            
            $comments .= $the_comment;
        }
        $comments .= (self::$fksdb->count($kk)?'</div>':'');
        
        if(!$c->position)
            $output = $event.$form.$comments;
        else
            $output = $event.$comments.$form;

        $this->html_event = $event;
        $this->html_form = $form;
        $this->html_comments = $comments;
        $this->html = $this->executeCallback($output);
        
        // Ausgabe
        $html = '<div'.$this->add_css.' class="'.trim('fks_comment_container '.$this->add_class).'" id="fks_comment_container_'.$this->block->vid.'">'.$this->html.'</div>';
        
        return $html;
    }

    public function getHookAttributes()
    {
        $self = array(
            'options' => $this->options,
            'comments' => $this->comments,
            'event' => $this->event,
            'html' => $this->html,
            'html_event' => $this->html_event,
            'html_form' => $this->html_form,
            'html_comments' => $this->html_comments
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }
}   
?>