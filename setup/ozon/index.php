<?

\Action::before('/setup/ozon/0', 'добавить выгрузку');

\Cron\Table::cabinetType(\Cabinet\Type::OZON);
\Cron\Table::types(10, 19);
\Cron\Table::root('/setup/ozon');
\Cron\Table::parse();