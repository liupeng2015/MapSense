from django.db import models

# Create your models here.

class User(models.Model):
    username = models.CharField(max_length=30)
    password = models.CharField(max_length=30)
    type = models.IntegerField()
    
    def __unicode_(self):
            return self.username  
          
#   test   test  test  
class newUser(models.Model):
    name = models.CharField(max_length=30)
    image= models.FileField(upload_to="./MapSense/php/import/uploaded/");
    
    
    def __unicode_(self):
            return self.name
        
class project(models.Model):
    pname = models.CharField(max_length=30)
    pcreater = models.CharField(max_length=30)
    ptime = models.DateTimeField(auto_now=True)
    puser = models.CharField(max_length=30)
    pip = models.CharField(max_length=30)
    pwd = models.CharField(max_length=30)
    
    def __unicode_(self):
            return self.pname
    
class data(models.Model):
    duser = models.CharField(max_length=30)
    dtime = models.DateField(auto_now=True)
    dbuild = models.CharField(max_length=30)
    dfloor = models.IntegerField()
    dproname = models.CharField(max_length=30)
    dproid = models.IntegerField()
     
    def __unicode_(self):
            return self.dproname+"_"+self.dbuild+"_"+self.dfloor   

        
      
