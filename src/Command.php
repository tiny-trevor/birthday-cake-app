<?php
    namespace Console;

    use Symfony\Component\Console\Command\Command as SymfonyCommand;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class Command extends SymfonyCommand
    {
    
        /**
         * Enum list of accepted filetypes
         */
        public const types = ['txt'];
        
        
        public function __construct()
        {
            parent::__construct();
        }
    
        /**
         * Generate basic output with title and send the provided argument (filepath) to be parsed into output data
         *
         * @param InputInterface $input
         * @param OutputInterface $output
         * @throws \Exception
         */
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
        
        private function getData(String $filepath)
        {
            
            if($this->validateFile($filepath)){
                return print_r($this->extractData($filepath), true);
            }
            else {
                throw new \Exception("An unknown error occurred.");
            }
            
        }
    
        public function extractData($filepath)
        {
            $file_array = file($filepath);
        
            $data = [];
        
            foreach($file_array as $file_line) {
            
                if(!$this->validateData($file_line)) {
                    throw new \Exception("Data must be in format of 'Name, yyyy-mm-dd'.");
                }
            
                $line_data = explode(",", $file_line);
            
                $data[trim($line_data[0])]= trim($line_data[1]);
            }
        
            return $data;
        
        }
    
        /**
         * Validate that the filepath provided:
         * Points to an existing file,
         * Is of type 'file'
         * And matches the enum of file extensions accepted.
         * Else throw an exception (caught by CakeCommand::execute()).
         *
         * @param $filepath
         * @return bool
         * @throws \Exception
         */
        public function validateFile($filepath)
        {
            
            if(!file_exists($filepath)) {
                throw new \Exception("File '{$filepath}' not found, please try again.");
            }
            
            if(!is_file($filepath)) {
                $filetype = filetype($filepath);
                throw new \Exception("Please provide a file. '{$filepath}' is a {$filetype}");
            }
            
            $file_ext = pathinfo($filepath, PATHINFO_EXTENSION);
            if(!in_array($file_ext, self::types)) {
                $file_types = implode(', ',self::types);
                throw new \Exception("File given is of type '{$file_ext}', only files of types '{$file_types}' are allowed");
            }
            
            return true;
        }
    
        /**
         * Validate that the passed line contains:
         * A comma (with optional whitespace, trimmed later)
         * A Date in the format of dddd-dd-dd
         * And that the line ends after that
         *
         * @param $file_line
         * @return bool
         */
        private function validateData($file_line)
        {
            $line_pattern = '/((,)(\s*)(\d{4})(-\d{2}){2})$/';
    
            if(preg_match($line_pattern, $file_line)){
                return true;
            }
            else {
                return false;
            }
        }
    
    
    
    }