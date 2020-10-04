<?php
    
    use Carbon\Carbon;
    use Console\CakeCommand;
    use org\bovigo\vfs\vfsStream;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Tester\CommandTester;
    
    final class ConsoleTest extends TestCase
    {
        private $file;
    
        protected function setUp(): void
        {
            //Sample Testing Data
            $this->file = [
                'birthdays.txt' =>
                    'Naomi, 1994-10-10
                    Julien, 1993-10-31
                    Flossie, 1995-10-20
                    Steve,1992-10-14
                    Pete, 1964-07-22
                    Mary,1989-06-21
                    Dave, 1986-06-26
                    Rob, 1950-07-05
                    Sam, 1990-07-13
                    Kate, 1985-07-14
                    Alex, 1976-07-20
                    Jen, 2000-07-21'
            ];
        }
        
        public function test_connect_to_console()
        {
            $application = new Application();
            
            //Given an Application
            $command = $application->add(new CakeCommand());
            $commandTester = new CommandTester($command);
            
            //And given a file path, using...
            //Abstract file system and populate using setup string
            $root = vfsStream::setup('root', null, $this->file);
            
            $commandTester->execute([
                //pass arguments to the helper
                'filepath' => $root->url().'/birthdays.txt',
            ]);
            
            //Verify that the console executes and shows the title of the Application
            $output = $commandTester->getDisplay();
            $this->assertStringContainsString('===Birthday Cakes===', $output);
            
        }
    
        public function test_console_shows_exception_message()
        {
            $application = new Application();
        
            //Given an Application
            $command = $application->add(new CakeCommand());
            $commandTester = new CommandTester($command);
        
            //And given a nonexistent file path
            $commandTester->execute([
                //pass arguments to the helper
                'filepath' => 'idonotexist.txt',
            ]);
        
            //Verify that the console executes and shows the error message
            $output = $commandTester->getDisplay();
            $this->assertStringContainsString("Error: ", $output);
        
        }
        
        public function test_function_success_creates_csv_file()
        {
            $application = new Application();
        
            //Given an Application
            $command = $application->add(new CakeCommand());
            $commandTester = new CommandTester($command);
    
            //And given a file path, using...
            //Abstract file system and populate using setup string
            $root = vfsStream::setup('root', null, $this->file);
    
            $commandTester->execute([
                //pass arguments to the helper
                'filepath' => $root->url().'/birthdays.txt',
            ]);
            
            //Construct the name of the csv file
            $date = (new Carbon())->format('d-m-Y');
            $filename = 'cakedays-'.$date.'.csv';
        
            //Verify that a csv file has been created
            $this->assertFileExists($filename);
        
        }
    
        public function test_console_displays_success_table()
        {
            $application = new Application();

            //Given an Application
            $command = $application->add(new CakeCommand());
            $commandTester = new CommandTester($command);

            //And given a file path, using...
            //Abstract file system and populate using setup string
            $root = vfsStream::setup('root', null, $this->file);

            $commandTester->execute([
                //pass arguments to the helper
                'filepath' => $root->url().'/birthdays.txt',
            ]);

            //Verify that the console executes and shows the title of the Application
            $output = $commandTester->getDisplay();
            $this->assertStringContainsString('Date', $output);
            $this->assertStringContainsString('Small Cakes', $output);
            $this->assertStringContainsString('Large Cakes', $output);
            $this->assertStringContainsString('Names', $output);

        }
        
        protected function tearDown():void
        {
            //Unset the sample testing data
            unset($this->file);
        }
    }