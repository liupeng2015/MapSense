#! -*- coding:utf-8 -*-
'''
Created on 2017年1月7日

@author: root
'''

from Map.models import User, newUser,project,data

def getProjects(user):
    result = []
    prolist = project.objects.filter(pcreater=user)
    for obj in prolist:
        result.append(obj.pname)
    return result


def getDataSet(user):

    datalist = data.objects.filter(duser=user)
    return datalist
    
    
    