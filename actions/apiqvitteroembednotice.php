<?php

  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
   ·                                                                            ·
   ·  Qvitter's Oembed response for notices                                     ·
   ·                                                                            ·
   - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
  ·                                                                             ·
  ·                                                                             ·
  ·                             Q V I T T E R                                   ·
  ·                                                                             ·
  ·                      https://git.gnu.io/h2p/Qvitter                         ·
  ·                                                                             ·
  ·                                                                             ·
  ·                                 <o)                                         ·
  ·                                  /_////                                     ·
  ·                                 (____/                                      ·
  ·                                          (o<                                ·
  ·                                   o> \\\\_\                                 ·
  ·                                 \\)   \____)                                ·
  ·                                                                             ·
  ·                                                                             ·
  ·                                                                             ·
  ·  Qvitter is free  software:  you can  redistribute it  and / or  modify it  ·
  ·  under the  terms of the GNU Affero General Public License as published by  ·
  ·  the Free Software Foundation,  either version three of the License or (at  ·
  ·  your option) any later version.                                            ·
  ·                                                                             ·
  ·  Qvitter is distributed  in hope that  it will be  useful but  WITHOUT ANY  ·
  ·  WARRANTY;  without even the implied warranty of MERCHANTABILTY or FITNESS  ·
  ·  FOR A PARTICULAR PURPOSE.  See the  GNU Affero General Public License for  ·
  ·  more details.                                                              ·
  ·                                                                             ·
  ·  You should have received a copy of the  GNU Affero General Public License  ·
  ·  along with Qvitter. If not, see <http://www.gnu.org/licenses/>.            ·
  ·                                                                             ·
  ·  Contact h@nnesmannerhe.im if you have any questions.                       ·
  ·                                                                             ·
  · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · · */


if (!defined('GNUSOCIAL')) { exit(1); }

class ApiQvitterOembedNoticeAction extends ApiAction
{

    var $id = null;
    var $format = null;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    protected function prepare(array $args=array())
    {
        parent::prepare($args);

		$this->id = $this->arg('id');
        $this->format = $this->arg('format');

        return true;
    }

    /**
     * Handle the request
     *
     * @param array $args $_REQUEST data (unused)
     *
     * @return void
     */
    protected function handle()
    {
        parent::handle();

		$notice = Notice::getKV('id',$this->id);

		if(!$notice instanceof Notice){
			// TRANS: Client error displayed in oEmbed action when notice not found.
			// TRANS: %s is a notice.
			$this->clientError(sprintf(_("Notice %s not found."),$this->id), 404);
		}
		$profile = $notice->getProfile();
		if (!$profile instanceof Profile) {
			// TRANS: Server error displayed in oEmbed action when notice has not profile.
			$this->serverError(_('Notice has no profile.'), 500);
		}
		$authorname = $profile->getFancyName();

        $oembed=array();
        $oembed['version']='1.0';
        $oembed['provider_name']=common_config('site', 'name');
        $oembed['provider_url']=common_root_url();

		// TRANS: oEmbed title. %1$s is the author name, %2$s is the creation date.
		$oembed['title'] = sprintf(_('%1$s\'s status on %2$s'),
			$authorname,
			common_exact_date($notice->created));
		$oembed['author_name']=$authorname;
		$oembed['author_url']=$profile->profileurl;
		$oembed['url']=$notice->getUrl();
		$oembed['html']=$notice->getRendered();

        if($this->format == 'json') {
            $this->initDocument('json');
            print json_encode($oembed);
            $this->endDocument('json');
        } elseif ($this->format == 'xml') {
            $this->initDocument('xml');
            $this->elementStart('oembed');
            foreach(array(
                        'version', 'type', 'provider_name',
                        'provider_url', 'title', 'author_name',
                        'author_url', 'url', 'html'
                        ) as $key) {
                if (isset($oembed[$key]) && $oembed[$key]!='') {
                    $this->element($key, null, $oembed[$key]);
                }
            }
            $this->elementEnd('oembed');
            $this->endDocument('xml');
        } else {
            $this->serverError(sprintf(_('Format %s not supported.'), $this->format), 501);
        }
    }
}
