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
        
            //Add the Title of the App
            $output->writeln([
                '===Birthday Cakes===',
                ''
            ]);
            
            //Pass input filepath to function
            $fileContents = $this->getData($input->getArgument('filepath'));
            
            //Write in the data from the file
            $output->writeln($fileContents);
            
        }
        
        private function getData(String $filepath) {
            
            if(!file_exists($filepath)) {
                throw new \Exception("File '{$filepath}' not found, please try again.");
            }
            
            return file($filepath);
        }
    }