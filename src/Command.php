<?php
    namespace Console;

    use Symfony\Component\Console\Command\Command as SymfonyCommand;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class Command extends SymfonyCommand
    {
        
        public function __construct()
        {
            parent::__construct();
        }
        
        protected function getBirthdayCakes(InputInterface $input, OutputInterface $output)
        {
        
            $output->writeln([
                '===Birthday Cakes===',
                ''
            ]);
            
            $output->writeln($input->getArgument('filepath'));
            
        }
    }