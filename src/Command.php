<?php
    namespace Console;

    use Carbon\Carbon;
    use Symfony\Component\Console\Command\Command as SymfonyCommand;
    use Symfony\Component\Console\Helper\Table;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class Command extends SymfonyCommand
    {
    
        /**
         * Enum list of accepted filetypes
         */
        public const types = ['txt', 'csv'];
    
        /**
         * @var array
         */
        public array $company_holidays;
        
    
        public function __construct()
        {
            parent::__construct();
            
            //Get an array of company holidays
            $this->company_holidays = $this->getCompanyHolidays();
    
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
            $filepath = $input->getArgument('filepath');
            
            // Processing input should return the filename of the new CSV
            if($filename = $this->processInput($filepath)) {
                $message = "\nCSV File successfully created and stored in: \n => " . realpath($filename) . "\n";
    
                // Successful response
                $output->writeln($this->getCakeASCII());
                
                //Display data in table in output
                $table = new Table($output);
                
                //Shortened titles for console width
                $table->setHeaders(['Date', 'Small Cakes', 'Large Cakes', 'Names']);
                
                //Display all rows
                $file_data = file($filename);
                array_shift($file_data);
                foreach($file_data as $file_line) {
                    $row = str_getcsv($file_line, ",", '"');
                    $table->addRow($row);
                }
                
                $table->render();
    
                // Add the success message to the output
                $output->writeln($message);
            }
            
        
            
        }
        
        private function getCakeASCII() {
            return '█▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀█
█░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░█
█░░░░░░░░░░░░░░▄░░░░░░░░░░░░░░░░░░░░░█
█░░░░░░░░░░░░░░░█░░░░░░░░░░░░░░░░░░░░█
█░░░░░░░░░░░▄▄░▄▀░░░░░░░░░░░░░░░░░░░░█
█░░░░░░░░▄██████▄░░░░░▄▄▄▄▄▄███▀█░░░░█
█░░░░░░▄█░███████░██▀▀▀░▄▄█▀█▄███░░░░█
█░░░░█▀▀▀░█████▀▀▄█▀▄▄▀▀▄▄███████░░░░█
█░░░░█▀▄▄▄▄███░▄▄█▀▀▄▄███████▀▀▄▄░░░░█
█░░░░█░░░░▀▀▀▀▀▀▄▄███████▀▀▄▄████░░░░█
█░░░░█░░░░░░░░░█████▀▀▀▄▄███████▀░░░░█
█░░░░█░░░░░░░░░█▀▀░▄▄███████▀█▄▄█░░░░█
█░░░░█░░░░░░░░░▄▄███████▀█▄██████░░░░█
█░░░░█░░░░░░░░░████▀▀▄▄███████▀▀░░░░░█
█░░░░█░░░░░░░░░▀▀▄▄███████▀▀░░░░░░░░░█
█░░░░█░░░░░░░░░███████▀▀░░░░░░░░░░░░░█
█░░░░░▀▄▄▄░░░░░███▀▀░░░░░░░░░░░░░░░░░█
█░░░░░░░░░▀▀▀▀▀▀░░░░░░░░░░░░░░░░░░░░░█
█▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄█';
        }
        
    
        private function processInput(String $filepath)
        {
            // If file passes validation
            if($this->validateFile($filepath)){
                
                // Return birthday list array from file
                $data = $this->extractData($filepath);
                
                // Map against Company Holidays and Weekends
                $map_workingdays = $this->mapWorkingDays($data);
                
                // Skip Birthdays or first working day after non-working day birthday
                $skip_birthdays = $this->skipBirthday($map_workingdays);
                
                // Get next possible cake day for each person, group by date
                $get_workingdays = $this->getNextWorkingDays($skip_birthdays);
                
                // Separate out to allow for cake-free days after every cake day and assign large vs small cakes
                $get_cakedays = $this->getCakeDays($get_workingdays);
                
                // Compile data into CSV and return filepath
                return $this->createCSV($get_cakedays);
                
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
                if(!$this->validatePattern($file_line)) {
                    throw new \Exception("Data must be in format of 'Name, yyyy-mm-dd'. ({$file_line})");
                }
            
                //Separate line & trim into Name=>Date
                list($name, $date_raw) = array_map('trim', explode(",", $file_line));
                
                try {
                    $birthday = new Carbon($date_raw);
                }
                catch(\Exception $e) {
                    throw new \Exception("Invalid Date Format for {$date_raw}");
                }
                
                //Get current year
                $current_year = Carbon::now()->format("Y");
                
                //Set birthday date to current year
                $birthday->setYear(intval($current_year));
                
                $data[$name]= $birthday;
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
         * Map the given Birthday Data against the Company Holidays & Weekends
         * If a Birthday falls on a Company Holiday or a Weekend, add a day, until it no longer does.
         *
         * Add results to an array of cake day data after the next possible working day has been found.
         *
         * @param $data
         * @return array
         * @throws \Exception
         */
        public function mapWorkingDays($data)
        {
            $workingdays = [];
            
            foreach($data as $name => $date) {
    
                // Keep adding a day while the next day falls on a company holiday or weekend
                while(in_array($date->format('m-d'), $this->company_holidays) || $date->isWeekend()) {
                    $date->addDay();
                }
    
                $workingdays[$name] = $date;
            }
            
            return $workingdays;
    
        }
    
        /**
         * All employees get their birthday off
         * Add a day to the date
         * NOTE: Do this AFTER mapping the company holidays to account for birthdays on company holidays, so that:
         * If the office is closed on an employee’s birthday, they get the next working day off.
         *
         * Also check against the company holidays and weekends to ensure they are skipped
         * Add results to an array of cake day data after the birthday or next working day thereafter has been skipped
         *
         * @param $data
         * @return mixed
         * @throws \Exception
         */
        public function skipBirthday($data)
        {
    
            $birthday_skipped = [];
            
            foreach($data as $name => $full_date) {
                
                // Add a day, and then repeat while the next day falls on a company holiday or weekend
                do {
                    $full_date->addDay();
                } while(in_array($full_date->format('m-d'), $this->company_holidays) || $full_date->isWeekend());
            
                $birthday_skipped[$name] = $full_date;
            
            }
        
            return $birthday_skipped;
        
        }
    
        /**
         * Sort dates
         *
         * @param $a
         * @param $b
         * @return false|int
         */
        private function sortDates($a, $b)
        {
            $t1 = strtotime($a['date']);
            $t2 = strtotime($b['date']);
            return $t1 - $t2;
        }
        
        /**
         * From the data provided, iterate through each given date to find cake recipients, and create list of cake days
         * number of cakes, as well as the names of those getting cake that day
         *
         * Add results to an array of cake day data where each cake day should be on the next available working day
         * after birthdays have been skipped, grouped by the date
         *
         * @param $data
         * @return mixed
         */
        public function getNextWorkingDays($data)
        {
            // Get list of unique dates in the cake day data
            $dates = array_unique(array_values($data));
            
            foreach($dates as $carbonDate) {
                $full_date = $carbonDate->format('Y-m-d');

                $working_days[$full_date]['date'] = $full_date;
                $working_days[$full_date]['cakes'] = 0;
                $working_days[$full_date]['names'] = '';

                foreach($data as $name => $date)
                {
                    //Add each name element of $data into the array, grouping together those with matching cake days
                    if($carbonDate == $date) {
                        // If names string is empty, add the name, else include a comma beforehand for legibility
                        $working_days[$full_date]['names'] = $working_days[$full_date]['names'] == '' ? $name : $working_days[$full_date]['names'] . ", " . $name;
                        $working_days[$full_date]['cakes']++;
                    }
                }
            }
            
            usort($working_days, array($this,'sortDates'));
        
            return $working_days;
        }
    
        /**
         * Given an array of all cakedays, apply the health conditions:
         * No two cake days can occur after one another, instead a large cake is provided on the second day
         * The day after a cake day is a cake-free day. Any cake due on a cake-free day should be moved to the next day
         *
         * Once satisfied, return the finalised list of cake days.
         *
         * @param $data
         * @return array
         * @throws \Exception
         */
        public function getCakeDays($data)
        {
            
            // Get all cake days into an array
            $dates = array_column($data, 'date');
    
            $cake_days = [];
            $array_num = 0;
            
            // For health reasons, the day after each cake must be cake-free.
            $cake_free_days = [];
            
            foreach($data as $cakeday) {
                
                $current_day = $cakeday['date'];
    
                // Get next day
                $next_day = (new Carbon($cakeday['date']))->addDay()->format('Y-m-d');
    
                //If two or more cakes days coincide, we instead provide one large cake to share.
                if($cakeday['cakes'] > 1) {
                    $sm = 0;
                    $lg = 1;
                }
                else {
                    $sm = 1;
                    $lg = 0;
                }
                
                // For health reasons, the day after each cake must be cake-free.
                $cake_free_day = (new Carbon($next_day))->addDay()->format('Y-m-d');
    
                // If there is to be cake two days in a row, we instead provide one large cake on the second day.
                // Any cakes due on a cakefree day are postponed to the next working day.
                if(in_array($next_day, $dates) && !in_array($next_day, $cake_free_days)) {
    
                    // Find data object for the next day
                    $next_cakeday = array_search($next_day, array_column($data, 'date'));
    
                    $date = $next_day;
                    $lg = 1;
                    $sm = 0;
                    $names = $cakeday['names'] . ', '.$data[$next_cakeday]['names'];
    
                    // For health reasons, the day after each cake must be cake-free.
                    $cake_free_days[] = $cake_free_day;
                    
                }
                // Any cakes due on a cakefree day are postponed to the next working day.
                else if(in_array($current_day, $cake_free_days)) {
    
                    // Postpone to next working day
                    $skip_day = new Carbon($current_day);
                    do {
                        $skip_day->addDay();
                    } while(in_array($skip_day->format('m-d'), $this->company_holidays) || $skip_day->isWeekend());
                    
                    $date = $skip_day->format('Y-m-d');
                    $names = $cakeday['names'];
    
                    // For health reasons, the day after each cake must be cake-free.
                    $cake_free_days[] = $cake_free_day;
                    
                }
                else {
                    // If the current cakeday date has already been processed, skip
                    if(array_search($cakeday['date'], array_column($cake_days, 'date')))
                    {
                        continue;
                    }
    
                    $date = $cakeday['date'];
                    $names = $cakeday['names'];
                }
    
                // Assign values to new array item
                $cake_days[$array_num]['date'] = $date;
                $cake_days[$array_num]['sm'] = $sm;
                $cake_days[$array_num]['lg'] = $lg;
                $cake_days[$array_num]['names'] = $names;
                
                $array_num++;
                
            }
    
            usort($cake_days, array($this,'sortDates'));
            
            return $cake_days;
        }
        
        /**
         * Create CSV file in current main directory (/birthday-cake) out of cake day data
         * Filename: 'cakedays-d-m-Y.csv'
         * Running the script twice on the same day will overwrite the file
         * Return filepath to newly created CSV
         *
         * @param $data
         * @return string
         * @throws \Exception
         */
        private function createCSV($data)
        {
            $date = (new Carbon())->format('d-m-Y');
            
            // Headers for the CSV
            $headers = [
                'Date',
                'Number of Small Cakes',
                'Number of Large Cakes',
                'Names of People'
            ];
    
            $filename = "cakedays-".$date.".csv";
            
            try {
                $file = fopen($filename, 'w');
    
                fputcsv($file, $headers);
    
                foreach($data as $datum) {
                    fputcsv($file, $datum);
                }
    
                fclose($file);
            }
            catch(\Exception $e) {
                throw new \Exception("CSV could not be created. Reason:\n{$e}");
            }

            return $filename;
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
        private function validatePattern($file_line)
        {
            // Line must contain a comma, followed by one or more spaces, followed by a date in format yyyy-mm-dd
            $line_pattern = '/((,)(\s*)(\d{4})(-\d{2}){2})$/';
    
            if(preg_match($line_pattern, $file_line)){
                return true;
            }
            else {
                return false;
            }
        }
    
    }