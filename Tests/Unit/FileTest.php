<?php
    
    use Console\CakeCommand;
    use org\bovigo\vfs\vfsStream;
    use PHPUnit\Framework\TestCase;
    
    final class FileTest extends TestCase
    {
        public function test_argument_file_must_exist()
        {
            $class = new CakeCommand();
            $root = vfsStream::setup('root');
    
            $this->expectException(Exception::class);
            $class->validateFile($root->url().'/idonotexist.txt');
        
        }
    
        public function test_argument_must_be_a_file()
        {
            $class = new CakeCommand();
            $root = vfsStream::setup('root', null, ['idonotexist']);
        
            $this->expectException(Exception::class);
            $class->validateFile($root->url().'/idonotexist/');
        
        }
    
        public function test_argument_file_must_be_an_accepted_file_extension()
        {
            $class = new CakeCommand();
            $root = vfsStream::setup('root');
        
            $this->expectException(Exception::class);
            $class->validateFile($root->url().'/idonotexist.jpeg');
        
        }
    }