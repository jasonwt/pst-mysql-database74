<?php

declare(strict_types=1);

use Pst\Database\Connections\DatabaseConnection;
use Pst\Database\Exceptions\DatabaseException;
use Pst\Database\Query\IQueryResults;
use Pst\Database\Query\QueryResults;

// show all errors and warnings
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../../vendor/autoload.php';

class RandomData {
    public static bool $seeded = false;
    public static int $seed = -1;

    const RANDOM_MALE_NAMES = [
        "James", "John", "Robert", "Michael", "William", "David", "Richard", "Joseph", "Thomas", "Charles", "Daniel", "Matthew", "Anthony", 
        "Donald", "Mark", "Paul", "Steven", "Andrew", "Kenneth", "Joshua", "George", "Kevin", "Brian", "Edward", "Ronald", "Timothy", "Jason", 
        "Jeffrey", "Ryan", "Jacob", "Gary", "Nicholas", "Eric", "Stephen", "Jonathan", "Larry", "Justin", "Scott", "Brandon", "Frank", "Benjamin", 
        "Gregory", "Samuel", "Raymond", "Patrick", "Alexander", "Jack", "Dennis", "Jerry", "Tyler", "Aaron", "Jose", "Henry", "Adam", "Douglas", 
        "Nathan", "Peter", "Zachary", "Kyle", "Walter", "Harold", "Jeremy", "Ethan", "Carl", "Keith", "Roger", "Gerald", "Christian", "Terry", 
        "Sean", "Arthur", "Austin", "Noah", "Lawrence", "Jesse", "Joe", "Bryan", "Billy", "Jordan", "Albert", "Dylan", "Bruce", "Willie", "Gabriel", 
        "Alan", "Juan", "Louis", "Jonathan", "Wayne", "Roy", "Ralph", "Randy", "Eugene", "Vincent", "Russell", "Elijah", "Louis", "Bobby", "Philip", 
        "Johnny", "Howard", "Victor", "Martin", "Craig", "Stanley", "Shawn", "Travis", "Bradley", "Leonard", "Earl", "Gabriel", "Jimmy", "Francis", 
        "Todd", "Derek", "Jared", "Carlos", "Melvin", "Alfred", "Cody", "Ray", "Joel", "Edwin", "Alex", "Ben", "Jerome", "Jeffery", "Franklin", 
        "Scott", "Dale", "Curtis", "Alex", "Isaac", "Leroy", "Cory", "Clifford", "Frederick", "Seth", "Dustin", "Pedro", "Derrick", "Lloyd",
        "Alvin", "Cameron", "Luis", "Calvin", "Oscar", "Clarence", "Warren", "Dean", "Greg", "Jorge", "Daryl", "Rick", "Brent", "Tyler", "Marc",
        "Ruben", "Brett", "Nathaniel", "Rafael", "Leslie", "Edgar", "Milton", "Raul", "Hector", "Levi", "Dwayne", "Glen", "Erick", "Darryl", "Barry",
        "Rick", "Miguel", "Theodore", "Armando", "Cecil", "Jamie", "Manuel", "Jay", "Wallace", "Dan", "Kelvin", "Alejandro", "Shaun", "Cesar", "Nelson",
        "Roy", "Terrance", "Marvin", "Fernando", "Lance", "Caleb", "Curt", "Warren", "Dean", "Greg", "Jorge", "Daryl", "Rick", "Brent", "Tyler", "Marc"
    ];

    const RANDOM_FEMALE_NAMES = [
        "Mary", "Patricia", "Jennifer", "Linda", "Elizabeth", "Barbara", "Susan", "Jessica", "Sarah", "Karen", "Nancy", "Lisa", "Betty", "Dorothy", 
        "Sandra", "Ashley", "Kimberly", "Donna", "Emily", "Michelle", "Carol", "Amanda", "Melissa", "Deborah", "Stephanie", "Rebecca", "Laura", "Sharon", 
        "Cynthia", "Kathleen", "Helen", "Amy", "Shirley", "Angela", "Anna", "Ruth", "Brenda", "Pamela", "Nicole", "Katherine", "Virginia", "Catherine", 
        "Christine", "Samantha", "Debra", "Janet", "Carolyn", "Rachel", "Heather", "Maria", "Diane", "Emma", "Julie", "Joyce", "Frances", "Evelyn", "Joan", 
        "Christina", "Kelly", "Victoria", "Lauren", "Martha", "Judith", "Cheryl", "Megan", "Andrea", "Olivia", "Ann", "Jean", "Alice", "Jacqueline", "Hannah", 
        "Doris", "Kathryn", "Gloria", "Teresa", "Sara", "Janice", "Julia", "Marie", "Madison", "Grace", "Judy", "Theresa", "Beverly", "Denise", "Marilyn", 
        "Amber", "Danielle", "Abigail", "Brittany", "Rose", "Diana", "Natalie", "Sophia", "Alexis", "Lori", "Kayla", "Jane", "Lillian", "Emily", "Megan", 
        "Alyssa", "Katie", "Emma", "Lauren", "Hannah", "Anna", "Kelsey", "Kaylee", "Allison", "Hailey", "Jessica", "Taylor", "Morgan", "Sydney", "Jasmine", 
        "Alexandra", "Madison", "Destiny", "Mackenzie", "Brooke", "Nicole", "Amanda", "Stephanie", "Shelby", "Grace", "Olivia", "Chelsea", "Sierra", "Abigail",
        "Sophia", "Samantha", "Elizabeth", "Makayla", "Kaitlyn", "Maria", "Andrea", "Rachel", "Katherine", "Megan", "Jenna", "Rebecca", "Vanessa", "Michelle",
        "Jordan", "Angel", "Alyssa", "Danielle", "Catherine", "Madeline", "Isabella", "Gabrielle", "Savannah", "Erin", "Amber", "Courtney", "Zoe", "Molly",
        "Paige", "Audrey", "Leah", "Brianna", "Caroline", "Avery", "Ella", "Natalie", "Brooklyn", "Claire", "Alexa", "Kylie", "Alexis", "Lily", "Gabriella",
        "Makenna", "Kaitlyn", "Ariana", "Bailey", "Kendall", "Mary", "Patricia", "Jennifer", "Linda", "Elizabeth", "Barbara", "Susan", "Jessica", "Sarah",
        "Karen", "Nancy", "Lisa", "Betty", "Dorothy", "Sandra", "Ashley", "Kimberly", "Donna", "Emily", "Michelle", "Carol", "Amanda", "Melissa", "Deborah",
        "Stephanie", "Rebecca", "Laura", "Sharon", "Cynthia", "Kathleen", "Helen", "Amy", "Shirley", "Angela", "Anna", "Ruth", "Brenda", "Pamela", "Nicole",
        "Katherine", "Virginia", "Catherine", "Christine", "Samantha", "Debra", "Janet", "Carolyn", "Rachel", "Heather", "Maria", "Diane", "Emma", "Julie",
        "Joyce", "Frances", "Evelyn", "Joan", "Christina", "Kelly", "Victoria", "Lauren", "Martha", "Judith", "Cheryl", "Megan", "Andrea", "Olivia", "Ann"
    ];

    const RANDOM_LAST_NAMES = [
        "Smith", "Johnson", "Williams", "Jones", "Brown", "Davis", "Miller", "Wilson", "Moore", "Taylor", "Anderson", "Thomas", "Jackson", 
        "White", "Harris", "Martin", "Thompson", "Garcia", "Martinez", "Robinson", "Clark", "Rodriguez", "Lewis", "Lee", "Walker", "Hall", 
        "Allen", "Young", "Hernandez", "King", "Wright", "Lopez", "Hill", "Scott", "Green", "Adams", "Baker", "Gonzalez", "Nelson", "Carter", 
        "Mitchell", "Perez", "Roberts", "Turner", "Phillips", "Campbell", "Parker", "Evans", "Edwards", "Collins", "Stewart", "Sanchez", "Morris", 
        "Rogers", "Reed", "Cook", "Morgan", "Bell", "Murphy", "Bailey", "Rivera", "Cooper", "Richardson", "Cox", "Howard", "Ward", "Torres", 
        "Peterson", "Gray", "Ramirez", "James", "Watson", "Brooks", "Kelly", "Sanders", "Price", "Bennett", "Wood", "Barnes", "Ross", "Henderson", 
        "Coleman", "Jenkins", "Perry", "Powell", "Long", "Patterson", "Hughes", "Flores", "Washington", "Butler", "Simmons", "Foster", "Gonzales", 
        "Bryant", "Alexander", "Russell", "Griffin", "Diaz", "Hayes", "Myers", "Ford", "Hamilton", "Graham", "Sullivan", "Wallace", "Woods", "Cole",
        "West", "Jordan", "Owens", "Reynolds", "Fisher", "Ellis", "Harrison", "Gibson", "Mcdonald", "Cruz", "Marshall", "Ortiz", "Gomez", "Murray",
        "Freeman", "Wells", "Webb", "Simpson", "Stevens", "Tucker", "Porter", "Hunter", "Hicks", "Crawford", "Henry", "Boyd", "Mason", "Morales",
        "Kennedy", "Warren", "Dixon", "Ramos", "Reyes", "Burns", "Gordon", "Shaw", "Holmes", "Rice", "Robertson", "Hunt", "Black", "Daniels",
        "Palmer", "Mills", "Nichols", "Grant", "Knight", "Ferguson", "Rose", "Stone", "Hawkins", "Dunn", "Perkins", "Hudson", "Spencer", "Gardner",
        "Stephens", "Payne", "Pierce", "Berry", "Matthews", "Arnold", "Wagner", "Willis", "Ray", "Watkins", "Olson", "Carroll", "Duncan", "Snyder",
        "Hart", "Cunningham", "Bradley", "Lane", "Andrews", "Ruiz", "Harper", "Fox", "Riley", "Armstrong", "Carpenter", "Weaver", "Greene", "Lawrence",
        "Elliott", "Chavez", "Sims", "Austin", "Peters", "Kelley", "Franklin", "Lawson", "Fields", "Gutierrez", "Ryan", "Schmidt", "Carr", "Vasquez",
        "Castillo", "Wheeler", "Chapman", "Oliver", "Montgomery", "Richards", "Williamson", "Johnston", "Banks", "Meyer", "Bishop", "Mccoy", "Howell",
        "Alvarez", "Morrison", "Hansen", "Fernandez", "Garza", "Harvey", "Little", "Burton", "Stanley", "Nguyen", "George", "Jacobs", "Reid", "Kim",
        "Fuller", "Lynch", "Dean", "Gilbert", "Garrett", "Romero", "Welch", "Larson", "Frazier", "Burke", "Hanson", "Day", "Mendoza", "Moreno", "Bowman",
        "Medina", "Fowler", "Brewer", "Hoffman", "Carlson", "Silva", "Pearson", "Holland", "Douglas", "Fleming", "Jensen", "Vargas", "Byrd", "Davidson",
        "Hopkins", "May", "Terry", "Herrera", "Wade", "Soto", "Walters", "Curtis", "Neal", "Caldwell", "Lowe", "Jennings", "Barnett", "Graves", "Jimenez",
    ];

    const RANDOM_EMAIL_PROVIDERS = [
        "gmail.com", "hotmail.com", "yahoo.com", "outlook.com", "aol.com", "icloud.com", "protonmail.com", "zoho.com", "yandex.com", "mail.com",
        "gmx.com", "tutanota.com", "fastmail.com", "hushmail.com", "runbox.com", "countermail.com", "disroot.org", "mailbox.org", "posteo.de", "startmail.com",
        "kolabnow.com", "scryptmail.com", "ctemplar.com", "elude.in"
    ];

    public static function randomFirstNames(int $count): array {
        $firstNamesSet = array_merge(self::RANDOM_MALE_NAMES, self::RANDOM_FEMALE_NAMES);
    
        return array_map(function() use ($firstNamesSet) {
            return $firstNamesSet[mt_rand(0, count($firstNamesSet) - 1)];
        }, range(0, $count - 1));
    }

    public static function randomLastNames(int $count): array {
        $lastNamesSet = self::RANDOM_LAST_NAMES;

        return array_map(function() use ($lastNamesSet) {
            return $lastNamesSet[mt_rand(0, count($lastNamesSet) - 1)];
        }, range(0, $count - 1));
    }

    public static function randomAges(int $count, int $minAge = 0, int $maxAge = 100): array {
        return array_map(function() use ($minAge, $maxAge) {
            return mt_rand($minAge, $maxAge);
        }, range(0, $count - 1));
    }

    public static function randomEmails(int $count): array {
        $emailProviders = self::RANDOM_EMAIL_PROVIDERS;

        return array_map(function() use ($emailProviders) {
            $firstName = self::randomFirstNames(1)[0];
            $lastName = self::randomLastNames(1)[0];
            $provider = $emailProviders[mt_rand(0, count($emailProviders) - 1)];

            return strtolower($firstName . "." . $lastName[0] . "@" . $provider);
        }, range(0, $count - 1));
    }

    public static function randomPeople(int $count, int $seed = 0): array {
        
        mt_srand($seed);
        

        return array_map(function() {
            $gender = mt_rand(0, 1) == 0 ? "M" : "F";

            $firstName = $gender === "M" ? self::randomFirstNames(1)[0] : self::randomLastNames(1)[0];
            $lastName = self::randomLastNames(1)[0];

            $age = self::randomAges(1)[0];

            $emailProvider = self::RANDOM_EMAIL_PROVIDERS[mt_rand(0, count(self::RANDOM_EMAIL_PROVIDERS) - 1)];

            $email = null;
            if ($age > 12) {
                $emailFormat = mt_rand(0, 4);

                if ($emailFormat === 0) {
                    $email = strtolower($firstName . "." . $lastName . "@" . $emailProvider);
                } else if ($emailFormat === 1) {
                    $email = strtolower($firstName . "@" . $emailProvider);
                } else if ($emailFormat === 2) {
                    $email = strtolower($lastName . "@" . $emailProvider);
                } else if ($emailFormat === 2) {
                    $email = strtolower($firstName[0] . "." . $lastName . "@" . $emailProvider);
                } else {
                    $email = strtolower($firstName . "." . $lastName[0] . "@" . $emailProvider);
                }
            }

            $email = $age < 12 ? null : strtolower($firstName . "." . $lastName[0] . "@" . $emailProvider);

            return [
                "firstName" => $firstName,
                "lastName" => $lastName,
                "gender" => $gender,
                "age" => $age,
                "email" => $email
            ];
        }, range(0, $count - 1));
    }
}

class MockDatabaseConnection extends DatabaseConnection {
    private string $defaultSchema = "";
    private array $mockQueryResults = [];
    private string $lastInsertId = "";

    public function __construct(string $defaultSchema, array $mockQueryResults = []) {
        $this->defaultSchema = $defaultSchema;
        $this->mockQueryResults = $mockQueryResults;
    }

    protected function implQuery(string $query, array $parameters = []): IQueryResults {
        if (($queryResults = ($this->mockQueryResults[$query] ?? null)) === null) {
            throw new DatabaseException("Invalid query: '$query'");
        }
        
        $this->lastInsertId = $queryResults["lastInsertId"] ?? $this->lastInsertId;

        $results = $queryResults["results"] ?? [];
        $rowCount = $queryResults["rowCount"] ?? count($results);
        $columnCount = $rowCount === 0 ? 0 : count($queryResults["results"][0]);

        return new QueryResults(new ArrayIterator($queryResults["results"]), $rowCount, $columnCount);
    }

    public function getUsingSchema(): string {
        return $this->defaultSchema;
    }

    public function lastInsertId(): string {
        return $this->lastInsertId;
    }
}

$randomPeople = RandomData::randomPeople(50, 0);

$databaseQueries = [
    "select * from schema.users" => ["results" => $randomPeople],
    "select firstName from schema.users" => ["results" => array_map(fn($v) => $v["firstName"], $randomPeople)],
    "select firstName, lastName from schema.users" => ["results" => array_map(fn($v) => ["firstName" => $v["firstName"], "lastName" => $v["lastName"]], $randomPeople)],
    "select firstName, lastName, email from schema.users" => ["results" => array_map(fn($v) => ["firstName" => $v["firstName"], "lastName" => $v["lastName"], "email" => $v["email"]], $randomPeople)],
];




$mockDatabase = new MockDatabaseConnection("schema", $databaseQueries);

print_r($mockDatabase->query("select * from schema.users")->fetchAll());