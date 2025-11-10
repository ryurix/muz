<?php

\Cabinet\Model::load(\Page::arg());
if (\Cabinet\Model::valid()) {
	\Page::menu(-2, ['name'=>\Cabinet\Model::name()]);
}