# php-app-updater
This code provides a basic incremental update framework for PHP apps.
Updates consist of a collection of files which will be overwritten and the possibility to run PHP code once after installation.
## Demo
Version check

![step 1](https://i.imgur.com/NJh90Hz.png)


Update download & signature check

![step 2](https://i.imgur.com/NF8BTi3.png)


Update installation

![step 3](https://i.imgur.com/dGnV27d.png)


Version was updated and next update becomes available

![step 4](https://i.imgur.com/h7xeCju.png)

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
Initially, the following to steps have to be performed.
1. Create key pair by running createKeyPair.sh
2. Copy public_key.pem to the client root directory

# Building an update
1. Create a zip containing all files you wish to update(they will be overwritten, preserving folder structure contained in the zip)
2. Optional: add a file called "triggers.php" which will be executed once after overwriting the files
3. Place the zip in the folder "repo"
4. Run ./createSignature {zip name}
5. Run updateDB.sh {buildid} {version} {filename} {releasenotes} {optional: any character - if set the db will be updated instead of only printing the result}

# Further information
## version file
This file is used to store the current version of the client.

## /repo directory
This folder will contain all update zip files.

## db.json file
db.json contains all available updates and their signatures.
The build ID's determine what ?operation=update will consider then next update.
I recommend using steps of 10 for every version, this allows adding further in between at any time.

## Security Notice
Please do not use the provided demo public and private keys. Generate your own and keep the private key safe.
Furthermore add a XSRF protection for the client code.
