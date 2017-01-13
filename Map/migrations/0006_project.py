# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('Map', '0005_auto_20161118_1158'),
    ]

    operations = [
        migrations.CreateModel(
            name='project',
            fields=[
                ('id', models.AutoField(verbose_name='ID', serialize=False, auto_created=True, primary_key=True)),
                ('pname', models.CharField(max_length=30)),
                ('pcreater', models.CharField(max_length=30)),
                ('ptime', models.DateTimeField()),
                ('puser', models.CharField(max_length=30)),
                ('pip', models.CharField(max_length=30)),
                ('pwd', models.CharField(max_length=30)),
            ],
        ),
    ]
