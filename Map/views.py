#! -*- coding:utf-8 -*-
import os
import pwd
import time
from random import Random
from django import forms
from django.http.response import HttpResponse
from django.shortcuts import render, render_to_response
from django.template import loader, Context, RequestContext

from Map.models import User, newUser,project,data
from util import *
 
# Create your views here.
def logon(request):
    if request.POST.has_key('name') and request.POST.has_key('pwd') :

        namelist = request.POST.getlist('name')
        pwdlist = request.POST.getlist('pwd')
        files = request.FILES.getlist("img1")
        #verify the size of the list!
        size = len(namelist)
        for i in range(size):
            fname = str(namelist[i]) +"_"+ str(pwdlist[i])+".txt" 
            f = files[i]
            dest = open("./MapSense/php/import/uploaded/"+fname,'wb+')
            for c in f.chunks():
                dest.write(c)
                dest.close()
        openfile = open("aa.txt")
        lines  = openfile.readlines()

        current = []
        result = {}
        count = 1;
        for i in range(len(lines)):
            if lines[i].startswith("*"):
                result[count]=current
              
                count = count + 1
                current = []
                current.append(lines[i])
            else:
                current.append(lines[i])
       
            if i==len(lines)-1:
                result[count]=current
                

                 
        for f in files:
            dest = open("./MapSense/php/import/uploaded/"+f.name,'wb+')
            for c in f.chunks():
                dest.write(c)
                dest.close()             
                
        return render_to_response('login.html',{"testhehe":result})
    else:
        return render_to_response('test.html',context_instance = RequestContext(request))
     
  
def login(request):
    errors = []

    if request.POST.has_key('name') and request.POST.has_key('pwd') :
        print request.POST['name']
        
        if not request.POST.get('name',''):
            errors.append("please enter name!")
        if not request.POST.get('pwd','') :
            errors.append("please entre password!")
        if not errors:  
            name = request.POST['name']
            pwd = request.POST['pwd']
            try:
                result  = User.objects.get(username=name,password=pwd)

                request.session['user']=name
                
                print "loing chenggong !" # successs!
                return render_to_response('detail.html',{'name':name},context_instance = RequestContext(request))
            except :
                errors.append("name or password is invalid!")

                print errors
                return render_to_response('main.html',{'error':errors})
        else:
            return render_to_response('main.html',{'error':errors},context_instance = RequestContext(request))
    else:

        return  render_to_response('main.html')

def createFile(request):

    if request.session.get('user') == None:
        return render_to_response('main.html')
    
    
    if request.POST.has_key('projectname') and request.POST.has_key('dbpwd')  and request.POST.has_key('dbip') and request.POST.has_key('dbuser'):
        
        projectname = request.POST.get('projectname')
        #  maybe there are some limitation on the projectname,but i don`t konw the details! 
        
        dbip = request.POST.get('dbip')
        dbpwd = request.POST.get('dbpwd')
        dbuser = request.POST.get('dbuser')
        filename = "/etc/location_db/"+projectname+"_db.conf"
        file = open(filename,'wb+')
        file.write(projectname+"\r\n")
        file.write(dbip+"\r\n")
        file.write(dbuser+"\r\n")
        file.write(dbpwd+"\r\n")
        file.close()
        
        
        return render_to_response('upfile.html',{'projectname':projectname},context_instance = RequestContext(request))

    else:
        render_to_response("success.html",{"errors":"please fill all the blanks!"})
        
def doAll(request):
 
    if request.session.get('user') == None:
        return render_to_response('main.html')
    
    projectname = request.POST['hiddenvalue']
    namelist = request.POST.getlist('build-num')
    pwdlist = request.POST.getlist('floor-num')
    filelist = request.FILES.getlist("file-up")

        #verify the size of the list!
    size = len(namelist)
    for i in range(size):
        fname = projectname+"_"+str(namelist[i]) +"_"+ str(pwdlist[i])+".svg" 
        f = filelist[i]
        dest = open("./MapSense/php/import/uploaded/"+fname,'wb+')
        for c in f.chunks():
            dest.write(c)
            dest.close()   

    currentdir = os.getcwd() 
    os.chdir("MapSense/php/import")
    #rname = random_name()+".txt"
    rname = str(int(time.time()))+".txt"
    os.system("./doAll.sh " + projectname + " > "+rname)
    openfile = open(rname)
    lines  = openfile.readlines()
    #os.system("rm -f bb.txt")
    print lines 
    current = []
    result = {}
    count = 1;
    for i in range(len(lines)):
        if lines[i].startswith("*"):
            result[count]=current
              
            count = count + 1
            current = []
            current.append(lines[i])
        else:
            current.append(lines[i])
       
        if i==len(lines)-1:
                result[count]=current
    
    os.chdir(currentdir)

    return render_to_response('log.html',{'testhehe':result})

#
class UserForm(forms.Form):
    name = forms.CharField(label="xingming ")
    img = forms.FileField()
    
    
def random_name(ranlength=6):
        str = ''
        chars = "AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz"
        length = len(chars)-1
        random = Random()
        for i in range(ranlength):
            str+=chars[random.randint(0, length)]
        return str

def newlogin(request):
    errors = []

    if request.POST.has_key('name') and request.POST.has_key('pwd') :
        print request.POST['name']
        if not request.POST.get('name',''):
            errors.append("please enter name!")
        if not request.POST.get('pwd','') :
            errors.append("please entre password!")
        if not errors:  
            name = request.POST['name']
            pwd = request.POST['pwd']
            try:
                result  = User.objects.get(username=name,password=pwd)

                request.session['user']=name
                
                print "loing chenggong !" # successs!
                return render_to_response('firstpage.html',{'name':name},context_instance = RequestContext(request))
            except :
                errors.append("name or password is invalid!")

                print errors
                return render_to_response('main.html',{'error':errors})
        else:
            return render_to_response('main.html',{'error':errors},context_instance = RequestContext(request))
    else:

        return  render_to_response('main.html')

def datasetMain(request):
    user = request.session.get('user')
    prolist = getProjects(user)
    dataset = getDataSet(user)
    return render_to_response('dataset.html',{'prolist':prolist,'datasets':dataset,"setcount":len(dataset)})

def firstpage(request):
    return  render_to_response('firstpage.html')

def createProject(request):
    return  render_to_response('createproject.html')

def newcreateFile(request):
    print "jinlian  sdf sdkfj sdkf sdkf "
    if request.session.get('user') == None:
        return render_to_response('main.html')


    
    if request.POST.has_key('projectname') and request.POST.has_key('dbpwd')  and request.POST.has_key('dbip') and request.POST.has_key('dbuser'):
        user= request.session.get('user')
   
        projectname = request.POST.get('projectname')
        #  maybe there are some limitation on the projectname,but i don`t konw the details! 
         
        dbip = request.POST.get('dbip')
        dbpwd = request.POST.get('dbpwd')
        dbuser = request.POST.get('dbuser')
        filename = "/etc/location_db/"+projectname+"_db.conf"
        file = open(filename,'wb+')
        file.write(projectname+"\r\n")
        file.write(dbip+"\r\n")
        file.write(dbuser+"\r\n")
        file.write(dbpwd+"\r\n")
        file.close()
        
        newpro = project(pname=projectname,pcreater=user,puser=dbuser,pip=dbip,pwd=dbpwd)
        newpro.save()
        # chaxun  dangqian  user  suo chuangjian de  project
          # canshu  user   cha  project
#  function to  return a list  that contains the user`s project list 
        prolist = getProjects(user)
      
   
        
        return render_to_response('dataset.html',{'projectname':projectname,'prolist':prolist})
    else:
        return render_to_response("createproject.html",{"errors":"please fill all the blanks!"})
      

def newdoAll(request):
 
    if request.session.get('user') == None:
        return render_to_response('main.html')
    user = request.session.get('user')
    projectname = request.POST.get('hiddenvalue')
    build = request.POST.get('build-num')
    floor = request.POST.get('floor-num')
    files = request.FILES.get("file-up")
    print projectname
    print floor
    print build
    print files
    


    fname = projectname+"_"+str(build)+"_"+str(floor)+".svg"
    dest=open("./MapSense/php/import/uploaded/"+fname,'wb+')
    for c in files.chunks():
        dest.write(c)
        dest.close()  
    

    newdata  = data(duser=user,dbuild=build,dfloor=int(floor),dproname=projectname,dproid="0")
    newdata.save()
    
    dataset = getDataSet(user)
    prolist = getProjects(user)
    currentdir = os.getcwd() 
    os.chdir("MapSense/php/import")
    #rname = random_name()+".txt"
    rname = str(int(time.time()))+".txt"
    os.system("./doAll.sh " + projectname + " > "+rname)
#     openfile = open(rname)
#     lines  = openfile.readlines()
#     #os.system("rm -f bb.txt")
#     print lines 
#     current = []
#     result = {}
#     count = 1;
#     for i in range(len(lines)):
#         if lines[i].startswith("*"):
#             result[count]=current
#               
#             count = count + 1
#             current = []
#             current.append(lines[i])
#         else:
#             current.append(lines[i])
#        
#         if i==len(lines)-1:
#                 result[count]=current
#     
    os.chdir(currentdir)
    
    return render_to_response('dataset.html',{'prolist':prolist,"datasets":dataset,"setcount":len(dataset)})

