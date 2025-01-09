# Save this as 'Get-DirectoryTree.ps1'

# Define the Unicode box-drawing characters as variables
$TEE = [char]0x251C + [char]0x2500 + [char]0x2500 + " "    # ├──
$L_CORNER = [char]0x2514 + [char]0x2500 + [char]0x2500 + " " # └──
$VERTICAL = [char]0x2502 + "   "                             # │   
$SPACE = "    "

# Collection to store all paths
$script:allPaths = @()

function Get-DirectoryTree {
    param (
        [string]$Path = ".",
        [string]$Indent = "",
        [bool]$IsLast = $true,
        [bool]$IsRoot = $true,
        [string]$RelativePath = ""
    )

    # Get the directory name
    $dirName = Split-Path $Path -Leaf

    # Update relative path
    $currentPath = if ($RelativePath -eq "") {
        if (-not ($IsRoot -and $dirName -eq "timebank")) { "/$dirName" } else { "" }
    }
    else {
        "$RelativePath/$dirName"
    }

    # Print the current directory, skip if it's "timebank" at root
    if (-not ($IsRoot -and $dirName -eq "timebank")) {
        if ($Indent -eq "") {
            Write-Host "$dirName/"
            if ($dirName -ne "timebank") {
                $script:allPaths += "$currentPath/"
            }
        }
        else {
            $prefix = if ($IsLast) { $L_CORNER } else { $TEE }
            Write-Host "$Indent$prefix$dirName"
            if ($item.PSIsContainer) {
                $script:allPaths += "$currentPath/"
            }
        }
    }

    # Get all items in the directory, excluding specific files and directories
    $items = Get-ChildItem -Path $Path | Where-Object {
        $_.Name -notin @(
            ".git", 
            "node_modules", 
            "Get-DirectoryTree.ps1"
        )
    } | Sort-Object Name

    # Process each item
    for ($i = 0; $i -lt $items.Count; $i++) {
        $item = $items[$i]
        $isLastItem = ($i -eq ($items.Count - 1))
        $newIndent = if ($IsRoot -and $dirName -eq "timebank") { "" } else { $Indent + $(if ($IsLast) { $SPACE } else { $VERTICAL }) }

        if ($item.PSIsContainer) {
            # If it's a directory, recurse
            Get-DirectoryTree -Path $item.FullName -Indent $newIndent -IsLast $isLastItem -IsRoot $false -RelativePath $currentPath
        }
        else {
            # If it's a file, print it
            $prefix = if ($isLastItem) { $L_CORNER } else { $TEE }
            $filePath = "$currentPath/$($item.Name)"
            Write-Host "$newIndent$prefix$($item.Name)"
            $script:allPaths += $filePath
        }
    }
}

# Make sure we're using UTF-8 encoding
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# Clear the paths array
$script:allPaths = @()

# Run the script
$targetPath = if ($args[0]) { $args[0] } else { "." }
Get-DirectoryTree -Path $targetPath -IsRoot $true

# Print the file paths list
Write-Host "`nFile paths:`n"
$script:allPaths | ForEach-Object {
    Write-Host $_
}