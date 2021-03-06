<?php
    namespace Console;
    
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class CakeCommand extends Command
    {
        /**
         * Set up application details
         */
        public function configure()
        {
            $this->setName('cake-days')
                ->setDescription('Get Number of Cakes and Dates based on the provided Birthday File')
                ->setHelp('Enter the File Path to a .txt file containing Birthdays in one-per-line format
                [Person Name],[Date of Birth (yyyy-mm-dd)] and a file containing dates and cakes will be outputted
                into the folder')
                ->addArgument('filepath', InputArgument::REQUIRED, 'The path to the txt file.');
        }
    
        /**
         * Execute the application and catch any exceptions thrown into the output, to be displayed to the user
         *
         * @param InputInterface $input
         * @param OutputInterface $output
         * @return int
         */
        public function execute(InputInterface $input, OutputInterface $output)
        {
            try {
                $this->getBirthdayCakes($input, $output);
            }
            catch(\Exception $e) {
                $output->writeln("<error>Error: </error>" .$e->getMessage());
            }
    
            return 0;
        }
    }