# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('Map', '0007_auto_20170107_0322'),
    ]

    operations = [
        migrations.CreateModel(
            name='data',
            fields=[
                ('id', models.AutoField(verbose_name='ID', serialize=False, auto_created=True, primary_key=True)),
                ('duser', models.CharField(max_length=30)),
                ('dtime', models.DateField(auto_now=True)),
                ('dbuild', models.CharField(max_length=30)),
                ('dfloor', models.IntegerField()),
                ('dproname', models.CharField(max_length=30)),
                ('dproid', models.IntegerField()),
            ],
        ),
    ]
