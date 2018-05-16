#! /usr/bin/env python
'''
Copyright 2018 Simon Hrabos. All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE FREEBSD PROJECT "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT
OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
'''
import sys
import Tkinter as tk
import os, subprocess, signal, psutil
import os

def kill_proc_tree(pid, including_parent = False, signal_to_use = signal.SIGINT):    
    parent = psutil.Process(pid)
    children = parent.children(recursive=True)
    for child in children:
        child.send_signal(signal_to_use)
    psutil.wait_procs(children, timeout=5)
    if including_parent:
        parent.send_signal(signal_to_use)
        parent.wait(1)

class Application(tk.Frame):
    def __init__(self, topics, master=None):
        tk.Frame.__init__(self, master)
        self.grid(padx=20, pady=10)
        self.createWidgets()
        self.topicsToSave = topics

    def setStatus(self, status):
        self.status['text'] = 'Status: ' + status

    def setDiskInfo(self):
        df = subprocess.Popen(["df", "/home"], stdout=subprocess.PIPE)
        output = df.communicate()[0]
        device, size, used, available, percent, mountpoint = output.split("\n")[1].split()
        self.diskInfo['text'] = 'Remaining disk space: %.3fGB (%s used)'%(float(available)/(1024*1024), percent)
        self.after(5000, self.setDiskInfo)

    def startRecording(self):
        self.setStatus("recording")
        self.saveButton = tk.Button(self, text='Stop & save record', command=self.saveRecord)
        self.saveButton.config(padx=20, pady=20, bg='#a0d0a0')
        self.saveButton.grid(row=0, column=0)
        self.rosbag = subprocess.Popen("rosbag record " + " ".join(self.topicsToSave), shell=True)
        os.system("ptpcam --set-property=0x5013 --val=0x8002")
        os.system("ptpcam -R 0x101C")

    def saveRecord(self):
        self.setStatus("saving")
        kill_proc_tree(self.rosbag.pid)
        print "#saved"
        self.quit()

    def cancelRecording(self):
        if hasattr(self, "rosbag"):
            kill_proc_tree(self.rosbag.pid)
        print "#canceled"
        self.quit()

    def createWidgets(self):
        self.startButton = tk.Button(self, text='Start recording', command=self.startRecording)
        self.startButton.config(padx=20, pady=20, bg='#a0d0a0')
        self.startButton.grid(row=0, column=0)

        self.cancelButton = tk.Button(self, text='Cancel & erase recording', command=self.cancelRecording)
        self.cancelButton.config(padx=20, pady=20, bg='#e0b0b0')
        self.cancelButton.grid(row=0, column=1)
        
        self.status = tk.Label(self)
        self.status.grid(row=1, column=0, columnspan=2, pady=10)
        self.setStatus('pending')

        self.diskInfo = tk.Label(self)
        self.diskInfo.grid(row=2, column=0, columnspan=2, pady=10)
        self.setDiskInfo()

app = Application(sys.argv[1:])
app.master.title('4RECON data recorder')
app.mainloop()
