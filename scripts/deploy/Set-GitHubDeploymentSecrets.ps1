[CmdletBinding()]
param(
    [ValidateSet('staging', 'production')]
    [string[]] $Environment = @('staging', 'production'),
    [string] $Repository = 'Zaineb-Messaoudi/Esprit_5TWIN7_BuilDream'
)

$ErrorActionPreference = 'Stop'

if (-not (Get-Command gh -ErrorAction SilentlyContinue)) {
    throw 'GitHub CLI (gh) is required. Install it, run gh auth login, then retry.'
}

gh auth status | Out-Host
$deployHost = Read-Host 'DEPLOY_HOST (server DNS name or IP)'
$deployUser = Read-Host 'DEPLOY_USER (SSH user)'
$deployPath = Read-Host 'DEPLOY_PATH (absolute server path)'
$sshKeyPath = Read-Host 'Path to the private OpenSSH key (never the .pub file)'

if (-not (Test-Path -LiteralPath $sshKeyPath -PathType Leaf)) {
    throw "SSH key not found: $sshKeyPath"
}

foreach ($environmentName in $Environment) {
    gh secret set DEPLOY_HOST --repo $Repository --env $environmentName --body $deployHost
    gh secret set DEPLOY_USER --repo $Repository --env $environmentName --body $deployUser
    gh secret set DEPLOY_PATH --repo $Repository --env $environmentName --body $deployPath
    Get-Content -LiteralPath $sshKeyPath -Raw |
        gh secret set DEPLOY_SSH_KEY --repo $Repository --env $environmentName
    Write-Host "Configured deployment secrets for $environmentName."
}
