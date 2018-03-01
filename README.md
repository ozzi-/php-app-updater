# php-app-updater
This code provides a basic update framework for PHP apps.

## Client Calls
```
   index.php                   index.php?step=download     index.php?step=install
   
   +---------------------+     +---------------------+     +---------------------+
   |                     |     |                     |     |                     |
   |                     |     |                     |     |                     |
   |                     |     |   Download and      |     |   Unpack and run    |
   |  Check for Updates  +----->   verify Update     +----->   triggers          |
   |                     |     |                     |     |                     |
   |                     |     |                     |     |                     |
   |                     |     |                     |     |                     |
   +---------------------+     +---------------------+     +---------------------+
```
## Server Calls
```
   ?operation=test            ?operation=newest          ?operation=update          ?operation=download
                                                         &buildid=X                 &buildid=X
   +--------------------+     +--------------------+     +--------------------+     +--------------------+
   |                    |     |                    |     |                    |     |                    |
   |                    |     |                    |     |                    |     |                    |
   | Check used to      |     | Returns the newest |     | Gets the next      |     | Returns the update |
   | determine if       |     | version available  |     | version for        |     | specified by the   |
   | server is running  |     |                    |     | build ID 'X'       |     | build ID           |
   | correctly          |     |                    |     |                    |     |                    |
   |                    |     |                    |     |                    |     |                    |
   |                    |     |                    |     |                    |     |                    |
   +--------------------+     +--------------------+     +--------------------+     +--------------------+
```

## Workflow
```
   +----------+                         +----------+
   |Client    | 1.                      | Server   |
   |          +------------------------>>          |
   |          |                         |          |
   |          |        check for        |          |
   |          |      newer version      |          |
   |          |                         |          |
   |          | 2.                      |          |
   |          <<------------------------+          |
   |          |                         |          |
   |          |        info about       |          |
   |          |      newest version     |          |
   |          |                         |          |
   |          | 3.                      |          |
   |          +------------------------>>          |
   |          |                         |          |
   |          |         download        |          |
   |          |      newest version     |          |
   |          |                         |          |
   |          |                         |          |
   |          +----------------------+  |          |
   |          | 4. check signature   |  |          |
   |          | 5. unpack zip        |  |          |
   |          | 6. opt: run triggers |  |          |
   |          <<---------------------+  |          |
   |          |                         |          |
   +----------+                         +----------+
   ```
# Setup 
1. Create key pair by running createKeyPair.sh
2. Copy public_key.pem to the client root directory

# Building an update
1. Create a zip containing all files you wish to update(they will be overwritten, preserving folder structure contained in the zip)
2. Optional: add a file called "triggers.php" which will be run once after overwriting the files
3. Place the zip in the folder "repo"
4. Run ./createSignature {zip name}
5. Run updateDB.sh {buildid} {version} {filename} {releasenotes} {optional: write to file (any character will work)}

# Further information
## version
This file is used to store the current version of the client.

## repo
This folder will contain all update zip files.

## db.json
db.json contains all available updates and their signatures.
The build ID's determine what ?operation=update will consider then next update.
I recommend using steps of 10 for every version, this allows adding further in between at any time.
