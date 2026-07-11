<?php
namespace frontend\components;

use yii\base\Widget;

class SupportNo  extends Widget{
    public $steps=[];
    public $slug = null;
    public $show_form_text=true;

    public function init(){
        parent::init();        
    }

    public function run(){
        return $this->render('support_no', ['steps'=>$this->steps, 'slug'=>$this->slug, 'show_form_text'=>$this->show_form_text]);         
    }
}
