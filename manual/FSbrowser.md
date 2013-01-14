#FSbrowser

###How does it work?

###Why is FSbrowser included with FSnode?
It is quite simple; to test each FSnode extension, you will need to be able to establish connection, scan the directories and read a file. In the future FSbrowser will let you do even more file management tasks (to test your FSnode extension).
###Why should I need to delete FSbrowser after [installation](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Installation.md)?
Well, you don't need to. But we advise you to! Having an other tool running (e.g. FSbrowser) opens the door to more security risks. Especially because FSbrowser enables FULL file system access, also outside your web-root. Instead of deleting, you could *chmod* the FSbrowser-directory to **0600**.
```bash
chmod 0600 ./FSbrowser -R
```
