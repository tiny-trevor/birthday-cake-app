<?php
    
    use Console\CakeCommand;
    use org\bovigo\vfs\vfsStream;
    use PHPUnit\Framework\TestCase;
    
    final class FileTest extends TestCase
    {
        public function test_argument_file_must_exist()
        {
            //Given a new instance of the application base class
            $class = new CakeCommand();
            
            //And Given a virtual empty root directory
            $root = vfsStream::setup('root');
    
            //Verify that an exception is called
            $this->expectException(Exception::class);
            
            //When provided with a non-existent file
            $class->validateFile($root->url().'/idonotexist.txt');
        
        }
    
        public function test_argument_must_be_a_file()
        {
            //Given a new instance of the application base class
            $class = new CakeCommand();
    
            //And Given a virtual empty root directory with a single folder
            $root = vfsStream::setup('root', null, ['directory']);
    
            //Verify that an exception is called
            $this->expectException(Exception::class);
            
            //When provided with an argument that isn't of type 'file'
            $class->validateFile($root->url().'/directory/');
        
        }
    
        public function test_argument_file_must_be_an_accepted_file_extension()
        {
            //Given a new instance of the application base class
            $class = new CakeCommand();
    
            //And Given a virtual empty root directory
            $root = vfsStream::setup('root');
    
            //Verify that an exception is called
            $this->expectException(Exception::class);
    
            //When provided with a file that does not match the accepted file extensions
            //JPEG would not be parseable, and therefore should never be an acceptable filetype
            $class->validateFile($root->url().'/idonotexist.jpeg');
        
        }
        
        public function test_company_holidays_file_must_exist()
        {
            //Given a new instance of the application base class
            $class = new CakeCommand();
            
            //And Given a virtual empty root directory
            $root = vfsStream::setup('root');
    
            //Verify that an exception is called
            $this->expectException(Exception::class);
    
            //When the companyholidays.json file does not exist
            $class->validateFile($root->url().'/companyholidays.json');
            
        }
    }