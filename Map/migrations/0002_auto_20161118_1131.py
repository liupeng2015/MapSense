# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('Map', '0001_initial'),
    ]

    operations = [
        migrations.CreateModel(
            name='newUser',
            fields=[
                ('id', models.AutoField(verbose_name='ID', serialize=False, auto_created=True, primary_key=True)),
                ('name', models.CharField(max_length=30)),
                ('image', models.FileField(upload_to=b'.\\MapSense\\php\\import\\uploaded')),
            ],
        ),
        migrations.DeleteModel(
            name='student',
        ),
    ]
