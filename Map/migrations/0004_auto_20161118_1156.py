# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('Map', '0003_auto_20161118_1154'),
    ]

    operations = [
        migrations.AlterField(
            model_name='newuser',
            name='image',
            field=models.FileField(upload_to=b'./php/'),
        ),
    ]
