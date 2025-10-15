<?

db_delete('menu', array('code'=>'/blank'));
db_insert('menu', array('code'=>'/blank', 'name'=>'Новая страница', 'up'=>'', 'w'=>100, 'type'=>1));
\Page::redirect('/menu/edit?url=/blank');

?>