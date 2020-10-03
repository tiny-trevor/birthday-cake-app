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
                    'Naomi, 1994-10-10\n
                    Julien, 1993-10-31\n
                    Flossie, 1995-10-20\n
                    Steve,1992-10-14\n
                    Pete, 1964-07-22\n
                    Mary,1989-06-21\n
                    Dave, 1986-06-26\n
                    Rob, 1950-07-05\n
                    Sam, 1990-07-13\n
                    Kate, 1985-07-14\n
                    Alex, 1976-07-20\n
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
        
        protected function tearDown():void
        {
            //Unset the sample testing data
            unset($this->file);
        }
    }