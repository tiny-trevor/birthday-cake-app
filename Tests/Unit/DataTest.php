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
                    'Julien, 1993-10-31
                    Flossie, 1995-10-20
                    Steve, 1992-10-14
                    Pete, 1964-07-22
                    Naomi, 1994-10-1033'
            ];
            
            //And Given a virtual root directory to hold the file
            $root = vfsStream::setup('root', null, $invalid_data_file);
        
            //Verify that an exception is called
            $this->expectException(Exception::class);
        
            //When provided with a non-existent file
            $class->extractData($root->url().'/invaliddata.txt');
        
        }
    
        public function test_data_must_have_correct_date_format()
        {
            //Given a new instance of the application base class
            $class = new CakeCommand();
        
            //With a file containing invalid data
            $invalid_data_file = [
                'invaliddata.txt' =>
                    'Julien, 1993-10-50
                    Flossie, 1995-10-20
                    Steve, 1992-10-14
                    Pete, 1964-07-22
                    Naomi, 1994-10-10'
            ];
        
            //And Given a virtual root directory to hold the file
            $root = vfsStream::setup('root', null, $invalid_data_file);
        
            //Verify that an exception is called
            $this->expectException(Exception::class);
        
            //When provided with a non-existent file
            $class->extractData($root->url().'/invaliddata.txt');
        
        }
        
        public function test_a_cake_day_will_never_fall_on_a_company_holiday()
        {
            //Given a new instance of the application base class
            $class = new CakeCommand();
    
            //With a filesystem containing a birthday and a company holidays file
            $data_files = [
                'folder' =>
                [
                    'birthdays.txt' =>
                        'Julien, 1993-10-31
                        Flossie, 1995-10-20
                        Steve, 1992-10-14
                        Pete, 1964-07-22
                        Mark, 1971-12-25
                        Naomi, 1994-10-10',
                    
                    'companyholidays.json' =>
                        '[
                            {
                                "name": "Christmas Day",
                                "date" : "12-25"
                            }
                        ]'
                ]
            ];
            
            //And Given a virtual root directory to hold the files
            $root = vfsStream::setup('root', null, $data_files);
            
            //When Getting the Data from the birthdays file
            $data = $class->extractData($root->url().'/folder/birthdays.txt');
            
            //And mapping it against the company holidays file
            $holidays_map = $class->mapCompanyHolidays($data);
            
            //No company holiday should appear on the resulting list
            $this->assertNotContains('12-25', $holidays_map);

            
        }
        
        public function test_someones_cake_day_will_never_fall_on_their_birthday()
        {
            //Given a new instance of the application base class
            $class = new CakeCommand();
    
            //With a filesystem containing a birthday file
            $data_files = [
                'folder' =>
                [
                    'birthdays.txt' =>
                        'Naomi, 1994-10-10',
                ]
            ];
            
            //And Given a virtual root directory to hold the files
            $root = vfsStream::setup('root', null, $data_files);
            
            //When Getting the Data from the birthdays file
            $data = $class->extractData($root->url().'/folder/birthdays.txt');
            
            //And mapping it against the company holidays file
            $holidays_map = $class->skipBirthday($data);
            
            //No company holiday should appear on the resulting list
            $this->assertNotContains('10-10', $holidays_map);

            
        }
        
        public function test_if_two_people_share_a_cake_day_they_get_one_large_cake()
        {
            //Given a new instance of the application base class
            $class = new CakeCommand();
    
            //With a filesystem containing a birthday file with two people sharing the same birthday
            $data_files = [
                'folder' =>
                [
                    'birthdays.txt' =>
                        'Julien, 1993-10-31
                        Letitia, 1993-10-31'
                ]
            ];
            
            //And Given a virtual root directory to hold the files
            $root = vfsStream::setup('root', null, $data_files);
            
            //When Getting the Data from the birthdays file
            $data = $class->extractData($root->url().'/folder/birthdays.txt');
            
            //And getting the relevant cake days
            $cake_days = $class->getCakeDays($data);
            
            //Get result of the lg and sm cakes value of the array
            $large_cakes = $cake_days[0]['lg'];
            $small_cakes = $cake_days[0]['sm'];
            
            //The resulting array should contain a key called lg inside 0
            $this->assertArrayHasKey('lg', $cake_days[0]);
            
            //The results should show 1 large cake
            $this->assertEquals('1', $large_cakes);
            
            //The results should show 0 small cakes
            $this->assertEquals('0', $small_cakes);
            
        }
    }
