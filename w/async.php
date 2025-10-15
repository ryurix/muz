<?php

/*

Не давно понадобилась возможность удобного вызова функции в потоке и получения её результата после отработки, т.е. работа в асинхронном режиме. 

В результате получился класс со статичными методами. Код представлен ниже.

*/


abstract class Async{
    //Получить объект
    static function get($self){
        return TThread::get($self);
    }
    //установить другую функция для результата
    static function setCallBack($self,$res){
        $self = Async::get($self);
        $GLOBALS['asyncCallBack'][$self->____name] = $res;
    }
    //запустить функцию через $ms, с параметрами $arg
    static function callAfter($self,$ms,$arg = null){       
        Timer::setInterval(function($timer) use ($self,$arg){
            $self = Async::get($self);
            if($arg)
                $self->____arg = $arg;
            if($GLOBALS['asyncCallBack'][$self->____name])
                Async::Call($self->____name,false,$self->____arg);            
            Timer::clearTimer($timer);
        },$ms);
    }
    //вызвать результативную функцию через $ms, с результатом $res
    static function resultAfter($self,$ms,$res){ 
        Timer::setInterval(function($timer) use ($self,$res){
            $self = Async::get($self);
            $v = $GLOBALS['asyncCallBack'][$self->____name];  
            if(!$v) return;
            $v($self,$res);
            Timer::clearTimer($timer);
        },$ms);
    }
    //вызвать результат, только без задержки
    static function result($self,$res){ 
        $self = Async::get($self);
        $v = $GLOBALS['asyncCallBack'][$self->____name];  
        if(!$v) return;
        $v($self,$res);
    }
    //вызвать функцию, указав только $self и параметры
    static function callEx($self,$arg = null){
        $self = Async::get($self);
        if($arg)
            $self->____arg = $arg;
        Async::Call($self->____name,false,$self->____arg);
    }
    //Очистить
    static function Clear($self){
        $self = Async::get($self);
        unset($GLOBALS['asyncCallBack'][$self->____name]);
    }
    //служебная функция, использовать не будем 
    static function syncResult($self,$name,$res){
        $v = $GLOBALS['asyncCallBack'][$name];
        if($v)
            $v($self,$res);
    }
    //служебная функция, использовать не будем 
    static function threadCall($t){
        if(!$GLOBALS['THREAD_SELF']) return;
        $self = TThread::get($t);
        $res = call_user_func_array($self->____name, $self->____arg);
        sync('Async::syncResult',array($t,$self->____name,$res));
    }
    /*вызвать функцию в потоке
    $name - имя функции. Например: file_get_contents
    $callBack - Функция, которой будет передан результат выше указанной функции. 
    $args - массив параметров
    */
    static function Call($name,$callBack, $args = null){
        if($GLOBALS['THREAD_SELF']) return;
        if($callBack)
            $GLOBALS['asyncCallBack'][$name] = $callBack; 
        $name = (string)$name;
        list($object, $method_name) = explode('::', $name);
        if((!function_exists($name))&&(!method_exists($object, $method_name)))
            return false;
        $t = new TThread('Async::threadCall');        
        $t->____name = $name;
        $t->____arg = $args; 
        $t->resume(); 
        return true;
    }
}

/*

Как это использовать?

На самом деле вариантов относительно много :)
Покажу наиболее используемые.

Простой вызов функции в потоке:
Вызываем функцию file_get_contents и после её отработки получаем результат и грузим его в компонент картинка. 
Самое главное программа абсолютно не зависает!
$url = 'http://goo.gl/xkW0R';
Async::Call('file_get_contents',function($self,$content){
        c("image1")->picture->loadFromStr($content,'jpg');
},array($url));


Асинхронный таймер:
Например нужно обновление картинки в потоке со статусом сервера и отображение в программе.
//Ссылка на картинку
$url = 'ссылка убрана по техническим причинам';
//создаем анонимную функция и передаем её в себя же через use
$asyncTimer = function($self,$img)use(&$asyncTimer,$url){
        //если есть что грузить, грузим
        if($img)
                c("image1")->picture->loadFromStr($img,'png');

        //если не в потоке, то передаем имя функции для вызова, анонимную функцию,ссылку и запускаем
        if(!$self)
                Async::Call('file_get_contents',$asyncTimer,array($url));
        else //иначе запускаем через 1000 мс, параметры уже в $self 
                Async::callAfter($self,1000);
};
$asyncTimer(false,false); //запускаем таймер

*/

?>