param(
    [string]$Profile = "",
    [switch]$NonInteractive,
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$RemainingArgs
)

$arguments = @("framework", "setup")

for ($i = 0; $i -lt $RemainingArgs.Count; $i++) {
    $arg = $RemainingArgs[$i]
    if ($arg -eq "--profile" -and ($i + 1) -lt $RemainingArgs.Count) {
        $Profile = $RemainingArgs[$i + 1]
        $i++
        continue
    }
    if ($arg.StartsWith("--profile=")) {
        $Profile = $arg.Substring("--profile=".Length)
        continue
    }
    if ($arg -eq "--non-interactive") {
        $NonInteractive = $true
        continue
    }
}

if ($Profile -ne "") {
    $profileValue = $Profile.ToLowerInvariant()
    if ($profileValue -ne "local" -and $profileValue -ne "cloud") {
        Write-Error "Invalid profile: $Profile (expected local|cloud)"
        exit 1
    }
    $arguments += "--profile=$profileValue"
}

if ($NonInteractive) {
    $arguments += "--non-interactive"
}

php @arguments
