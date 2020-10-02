<?php
    
    use Console\CakeCommand;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Tester\CommandTester;
    
    final class ConsoleTest extends TestCase
    {
        public function test_connect_to_console()
        {
            $application = new Application();
            
            //Given an Application
            $command = $application->add(new CakeCommand());
            $commandTester = new CommandTester($command);
            
            //And given a file path
            $commandTester->execute([
                //pass arguments to the helper
                'filepath' => 'birthdays.txt',
            ]);
            
            //Verify that the console executes and shows the title of the Application
            $output = $commandTester->getDisplay();
            $this->assertStringContainsString('===Birthday Cakes===', $output);
            
        }
    }