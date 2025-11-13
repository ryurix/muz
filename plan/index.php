<?

\Action::before('/plan/0', 'добавить задачу');

\Cron\Table::types(100);
\Cron\Table::root('/plan');
\Cron\Table::parse();