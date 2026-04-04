param(
    [ValidateSet("local", "cloud")]
    [string]$Profile = "",
    [switch]$NonInteractive
)

$arguments = @("framework", "setup")

if ($Profile -ne "") {
    $arguments += "--profile=$Profile"
}

if ($NonInteractive) {
    $arguments += "--non-interactive"
}

php @arguments

