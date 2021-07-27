# Related Content

## Description

Plugin for Omeka Classic. Once installed and active, when showing an Item a series of suggestions will be displayed at the bottom of the page, according to different parameters set in configuration.

## Installation
Uncompress files and rename plugin folder "RelatedContent".

Then install it like any other Omeka plugin.

## Weights configuration
All available elements and some extra criteria can be used to find related content: *Subject*, *Creator*, *Contributor*, *Date* and *Type* fields and *Collection*, *Tags* and *Item Type* are the suggested ones. Their relative weight (importance) can be adjusted in configuration page (for default elements: 2, 1.2, 1, 1.5, 0.5, 0.5, 2 and 0.5). You might want to experiment with different values, although it's probably a good idea to keep *Subject* and *Tags* as the heaviest.

## Constraints configuration
When applied to a criterion, a constraint excludes any result not belonging to that criterion. For example, to limit all suggestions passed by the plugin to Items created by a specific creator, one should check *Creator*'s "constraint" checkbox.

## Is Date
Every element can be marked as a date one, to accept a shorter value (useful to find related content by year instead of by full date). By default

## Warning
Use it at your own risk.

It’s always recommended to backup your files and your databases and to check your archives regularly so you can roll back if needed.

## Troubleshooting
See online issues on the <a href="https://github.com/DBinaghi/plugin-RelatedContent/issues" target="_blank">plugin issues</a> page on GitHub.

## License
This plugin is published under the <a href="https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html" target="_blank">CeCILL v2.1</a> licence, compatible with <a href="https://www.gnu.org/licenses/gpl-3.0.html" target="_blank">GNU/GPL</a> and approved by <a href="https://www.fsf.org/" target="_blank">FSF</a> and <a href="http://opensource.org/" target="_blank">OSI</a>.

In consideration of access to the source code and the rights to copy, modify and redistribute granted by the license, users are provided only with a limited warranty and the software’s author, the holder of the economic rights, and the successive licensors only have limited liability.

In this respect, the risks associated with loading, using, modifying and/or developing or reproducing the software by the user are brought to the user’s attention, given its Free Software status, which may make it complicated to use, with the result that its use is reserved for developers and experienced professionals having in-depth computer knowledge. Users are therefore encouraged to load and test the suitability of the software as regards their requirements in conditions enabling the security of their systems and/or data to be ensured and, more generally, to use and operate it in the same conditions of security. This Agreement may be freely reproduced and published, provided it is not altered, and that no provisions are either added or removed herefrom.

## Copyright
Copyright Daniele Binaghi, 2021
