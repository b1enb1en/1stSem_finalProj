session_start() - Starts or resumes a user session (used for login state).

session_destroy() - Ends the current session (used in logout).

header() - Sends a raw HTTP header (often used to redirect pages, e.g., Location: login.php).

require_once() - Includes a file (like db_init.php) and ensures it's only included once.

date() - Formats a local time/date (e.g., getting current day or time).

date_default_timezone_set() - Sets the default timezone used by all date/time functions.

is_dir() / mkdir() - Checks if a directory exists / Creates a new directory.

password_hash() - Creates a secure password hash (used in Registration).

password_verify() - Verifies that a password matches a hash (used in Login).

htmlspecialchars() - Converts special characters to HTML entities (prevents XSS attacks).

trim() - Strips whitespace from the beginning and end of a string.

strtotime() - Parses an English textual datetime description into a Unix timestamp.

PDO Methods	
prepare() - Prepares an SQL statement for execution (security against SQL injection).

execute() - Runs the prepared SQL statement.

fetch() - Fetches the next row from a result set.

fetchAll() - Fetches all remaining rows from a result set.

lastInsertId() - Returns the ID of the last inserted row or sequence value.
