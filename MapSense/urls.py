"""MapSense URL Configuration

The `urlpatterns` list routes URLs to views. For more information please see:
    https://docs.djangoproject.com/en/1.8/topics/http/urls/
Examples:
Function views
    1. Add an import:  from my_app import views
    2. Add a URL to urlpatterns:  url(r'^$', views.home, name='home')
Class-based views
    1. Add an import:  from other_app.views import Home
    2. Add a URL to urlpatterns:  url(r'^$', Home.as_view(), name='home')
Including another URLconf
    1. Add a URL to urlpatterns:  url(r'^blog/', include('blog.urls'))
"""
from django.conf.urls import include, url
from django.contrib import admin
from Map.views import login,doAll,createFile,logon,newlogin,datasetMain,createProject,newcreateFile,newdoAll,firstpage
urlpatterns = [
    url(r'^admin/', include(admin.site.urls)),
    url(r'^login/aaa/',login),
    url(r'^testanddel/',logon),
    url(r'^deal/',doAll),
    #url(r'^register',register),
    
    url(r'^project',createFile),
    url(r'^new/login/',newlogin),
    url(r'^new/welcome/',firstpage),
    url(r'^new/firstpage/',newlogin),
    url(r'^new/dataset/',datasetMain),
    url(r'^new/createpro/',createProject),
    url(r'^new/createfile/',newcreateFile),
    url(r'^new/creatdata/',newdoAll),
    ]

