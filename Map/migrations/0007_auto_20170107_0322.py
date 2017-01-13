# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('Map', '0006_project'),
    ]

    operations = [
        migrations.AlterField(
            model_name='project',
            name='ptime',
            field=models.DateTimeField(auto_now=True),
        ),
    ]
