# Birthday Cake Console App
A console app that takes a text file of names and birthdays and returns a list of days when employees should get cake

**Setup:**
1. `git clone` repo
2. `cd birthday-cake-app`
3. `composer install`
4. Create file with test data in format .txt or .csv (see below for expected data structure)
5. Run `./console cake-days file/to/path.txt` OR `php console cake-days file/to/path.txt`
  -> To use sample test data, run `./console cake-days nametest.txt` OR `php console cake-days nametest.txt`

**Process:**
Given a file in format TXT or CSV
With data in format [Person Name],[Date of Birth (yyyy-mm-dd)] (i.e. `Naomi, 1994-10-10`) (see nametest.txt for sample data)
The console should output a success message 
Including a table of the processed data
As well as saving a file into the current working directory (full path to file will be shown on console screen).

**The following restrictions decide when the employees should get a cake:**
- Employees get their birthday off, and if their birthday is not a working day, they get the next working day off
- The office has set "Company Holidays" as specified in the companyholidays.json file
- If an employee should get a cake on a non-working day, they instead get the cake the next possible working day
- If two employees should get cakes on consecutive days, they both get a cake on the second day instead
- There can be no two cake days in a row
- If only one employee is getting cake, a small cake is provided
- If more than one employee get cake, a large cake is provided

**Built using:**
- Symfony Console Component
- Carbon API
- vfsStream
- PHPUnit


