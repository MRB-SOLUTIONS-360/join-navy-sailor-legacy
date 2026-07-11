<?php
namespace common\enumClass;

enum Status
{
    case ACTIVE;
    case INACTIVE;     
    
    public function selected(): string
    {
        return match($this) 
        {
            Status::ACTIVE => 'Active',   
            Status::INACTIVE => 'Inactive',  
        };
    }

    public function dropDown()
    {
        return [
            Status::ACTIVE => 'Active',   
            Status::INACTIVE => 'Inactive',
        ]; 
    }
}




// abstract class DaysOfWeek
// {
//     const Sunday = 0;
//     const Monday = 1;
//     // etc.
// }

// https://www.php.net/releases/8.2/en.php