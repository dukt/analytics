<?php

class Craft extends BaseDataSource
{
    /*
    Area:
        - Entries
            - News
            - Blog
            - Updates
        - Users
            - Admins
            - Members
            - Editors
    Pie/Table:
        - Dimension
            - Sections
            - Element Types
            - Categories
            - Tags
            - Assets

    Entries by Section, Category, Tag
    Products by Product Type, Category, Tag

    if($entry->sectionId == $sectionId)
    if($entry->sectionId == $sectionId)

    */

    public function area() {}
    public function table() {}
    public function getSettingsHtml()
    {

    }
}
