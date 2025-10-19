<?php

class Action extends \Flydom\Action
{

	protected static function addClass($action) {
		return !empty($action['class']) ? ' '.$action['class'] : '';
	}

	protected static function item($action) {
		if ($action['here'] ?? false) {
			return '<a href="'.$action['href'].'" class="btn btn-sm btn-secondary my-1 mx-1'.static::addClass($action).'" disabled>'.$action['name']."</a>\n";
		} else {
			return '<a href="'.$action['href'].'" class="btn btn-sm btn-outline-secondary my-1 mx-1'.static::addClass($action).'">'.$action['name']."</a>\n";
		}
	}

	protected static function dropdownItem($action) {
		if ($action['here'] ?? false) {
			return '<a class="dropdown-item active'.static::addClass($action).'" href="'.$action['href'].'">'.$action['name']."</a>\n";
		} else {
			return '<a class="dropdown-item'.static::addClass($action).'" href="'.$action['href'].'">'.$action['name']."</a>\n";
		}
	}

	protected static function dropdown($action) {
		$s = '
<div class="dropdown d-sm-none d-md-block">
<a href="'.$action['href'].'" class="btn btn-sm btn-outline-default my-1 mx-1 dropdown-toggle'.static::addClass($action).'" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$action['name'].'</a>
<div class="dropdown-menu">';

		foreach ($action['/'] as $i) { $s.= static::dropdownItem($i); }

		return $s.'</div></div>';
	}


	protected static function buildLine($actions) {
		$list = [];
		foreach ($actions as $k=>$v) {
			$list[$k] = static::buildOne($v);
		}

		return '
<div class="container">
	<nav class="navbar navbar-expand-lg navbar-light bg-light">
'.implode('', $list).'
	</nav>
</div>';
	}
}