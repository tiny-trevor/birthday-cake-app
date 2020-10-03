<?php
    namespace Console;

    use Carbon\Carbon;
    use DateTime;
    use Symfony\Component\Console\Command\Command as SymfonyCommand;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class Command extends SymfonyCommand
    {
    
        /**
         * Enum list of accepted filetypes
         */
        public const types = ['txt', 'csv'];
        
        
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
                $data = $this->extractData($filepath);
                $company_holidays_data = $this->mapCompanyHolidays($data);
                $skip_birthdays = $this->skipBirthday($company_holidays_data);
                
                return $skip_birthdays;
            }
            else {
                throw new \Exception("An unknown error occurred.");
            }
            
        }
    
        /**
         * Extract Data from given File and return as array
         *
         * @param $filepath
         * @return array
         * @throws \Exception
         */
        public function extractData($filepath)
        {
            $file_array = file($filepath);
        
            $data = [];
        
            foreach($file_array as $file_line) {
            
                //Make sure the line passes validation
                if(!$this->validateData($file_line)) {
                    throw new \Exception("Data must be in format of 'Name, yyyy-mm-dd'. ({$file_line})");
                }
            
                //Separate line & trim into Name=>Date
                list($name, $date_raw) = array_map('trim', explode(",", $file_line));
                
                $data[$name]= $date_raw;
            }
        
            return $data;
        
        }
    
        /**
         * Get the company holidays in an array of mm-dd, from the specified file
         * Throw an exception if the file does not exist
         *
         * @return array
         * @throws \Exception
         */
        public function getCompanyHolidays()
        {
            $holidays_file = __DIR__ . '/companyholidays.json';
        
            //TODO: (OPTIONAL) automatically make file?
            if(!file_exists($holidays_file)) {
                throw new \Exception("File '{$holidays_file}' not found. Please ensure it exists and try again");
            }
        
            $holidays_json = file_get_contents($holidays_file);
            $holidays_array = json_decode($holidays_json);
        
            $company_holidays = [];
        
            foreach ($holidays_array as $holiday) {
                array_push($company_holidays, $holiday->date);
            }
        
            return $company_holidays;
        }
    
        /**
         * Map the given Birthday Data against the Company Holidays
         * If a Birthday falls on a Company Holiday, add a day, until it no longer does.
         *
         * @param $data
         * @return array
         * @throws \Exception
         */
        public function mapCompanyHolidays($data)
        {
            //Get an array of company holidays
            $company_holidays = $this->getCompanyHolidays();
            
            $holidays_mapped = [];
              
            foreach($data as $name => $raw_date) {
                
                $full_date = new Carbon($raw_date);
                
                while(in_array($full_date->format('m-d'), $company_holidays)) {
                    $full_date->addDay();
                }
                
                $holidays_mapped[$name] = $full_date;
            }
            
            return $holidays_mapped;
    
        }
    
        /**
         * All employees get their birthday off
         * Add a day to the date
         * NOTE: Do this AFTER mapping the company holidays to account for birthdays on company holidays, so that:
         * If the office is closed on an employee’s birthday, they get the next working day off.
         * Also check against the company holidays to ensure they are skipped
         *
         * @param $data
         * @return mixed
         * @throws \Exception
         */
        public function skipBirthday($data)
        {
            foreach($data as $name => $raw_date) {
            
                $full_date = new Carbon($raw_date);
                $full_date->addDay();
    
                //Get an array of company holidays
                $company_holidays = $this->getCompanyHolidays();
    
                while(in_array($full_date->format('m-d'), $company_holidays)) {
                    $full_date->addDay();
                }
            
                $birthday_skipped[$name] = $full_date;
            
            }
        
            return $birthday_skipped;
        
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
            //TODO: Date must be a real date.
            $line_pattern = '/((,)(\s*)(\d{4})(-\d{2}){2})$/';
    
            if(preg_match($line_pattern, $file_line)){
                return true;
            }
            else {
                return false;
            }
        }
    
    
    
    }