<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use App\Api\v1\Controllers\RichTextController;

class Comments extends Model
{
	use PostgisTrait;
    protected $table = 'comments';
	protected $postgisFields = [
	  'geolocation' => Point::class,
	]; 
	protected $fillable = array('user_id', 'content', 'geolocation');

	public function users() {
    	return $this->belongsTo('App\Users'); 
    }
    /**
     * The hashtags that this text contains.
     */
    public function hashtags()
    {
        return $this->morphToMany('App\Hashtags', 'hashtaggable'); 
    }
    
    /**
     * The hashtags that this text contains.
     */
    public function ats()
    {
        return $this->morphToMany('App\Users', 'useratable');
    }
    
    public function processRichtext() {
    	RichTextController::addHashtagFromRichtext($this->id, $this->content, 'Comments');
        RichTextController::atUsersFromRichtext($this->id, $this->content, 'Comments');
    }
    
    public function updateRichtext($old_content) {
        RichTextController::deleteHashtagFromRichtext($this->id, $old_content, 'Comments');
        RichTextController::deleteAtUsersFromRichtext($this->id, $old_content, 'Comments');
        RichTextController::addHashtagFromRichtext($this->id, $this->content, 'Comments');
        RichTextController::atUsersFromRichtext($this->id, $this->content, 'Comments');
        
    }
    
     public function deleteRichtext($old_content) {
        RichTextController::deleteHashtagFromRichtext($this->id, $old_content, 'Comments');
        RichTextController::deleteAtUsersFromRichtext($this->id, $old_content, 'Comments');
    }
    
}
