<?php
namespace frontend\components;

use yii\base\Widget;

class StepAndSupport  extends Widget{
    public $steps=[];
    public $slug = null;

    public function init(){
        parent::init();        
    }

    public function run(){
        return $this->render('step_and_support', ['steps'=>$this->steps, 'slug'=>$this->slug]);         
    }
}
