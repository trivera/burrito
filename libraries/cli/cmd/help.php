<?php

if (empty($args)) echo <<<EOD
{$tty->h1('Burrito help')}

	general usage: {$tty->func('burrito')} {$tty->ghost('<cmd>')} [{$tty->ghost('<arg1>')} {$tty->ghost('<arg2>')} ...]
	
	
	
{$tty->h1('Commands')}

	{$tty->cmd('install')} [{$tty->arg('cli')}|{$tty->arg('config')}|{$tty->default_arg('all')}] - install burrito components
		
		{$tty->arg('cli')}	installs burrito command line interface helper to /usr/local/bin
			to update, run this command again
		
		{$tty->arg('config')}	installs burrito config templates to config/burrito/
		
	
	{$tty->cmd('sync')} [{$tty->arg('data')}|{$tty->arg('files')}|{$tty->default_arg('all')}] - synchronize data and/or files with the production environment


EOD;
