# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('Map', '0004_auto_20161118_1156'),
    ]

    operations = [
        migrations.AlterField(
            model_name='newuser',
            name='image',
            field=models.FileField(upload_to=b'./MapSense/php/import/uploaded/'),
        ),
    ]
