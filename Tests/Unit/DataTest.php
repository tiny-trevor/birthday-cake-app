<?php
    
    use Console\CakeCommand;
    use org\bovigo\vfs\vfsStream;
    use PHPUnit\Framework\TestCase;
    
    final class DataTest extends TestCase
    {
        public function test_data_must_have_correct_format()
        {
            //Given a new instance of the application base class
            $class = new CakeCommand();
        
            //With a file containing invalid data
            $invalid_data_file = [
                'invaliddata.txt' =>
                    'Julien, 1993-10-31\n
                    Flossie, 1995-10-20\n
                    Steve, 1992-10-14\n
                    Pete, 1964-07-22\n
                    Naomi, 1994-10-1033\n'
            ];
            
            //And Given a virtual root directory to hold the file
            $root = vfsStream::setup('root', null, $invalid_data_file);
        
            //Verify that an exception is called
            $this->expectException(Exception::class);
        
            //When provided with a non-existent file
            $class->extractData($root->url().'/invaliddata.txt');
        
        }
    }
