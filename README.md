## Initial tasks

* Create laravel project structure
* Create database structure
  * table `files`
  * table `questions`
  * table `areas` (+ relation table `area_question`) for "games, media, mobile, music, sports, technology, video, ..."
  * table `categories`
  * table `segments`
  * table `answers` (+ relation tables `answer_category` and `answer_area`)
  * table `percents` (+ relation table `answer_percent`)
* Bash script to load XLS file datas into table
  * 1 entry in `files` with its name
  * 1 entry in `questions` per distinct "question" columns
  * 1 entry in `areas` per distinct "coverage area 1/2" columns
  * 1 entry in `categories` per distinct "browse category 1/2" columns
  * 1 entry in `segments` for "total", "gender", "age" and per distinct other segments
  * entries in `percents` with:
    * `file_id` int
    * `answer_id` int
    * `segment_id` int
    * `description` string
    * `value` float
* Create interface with graphics
  * Idea of main filters: file, question, area, category
  * Idea of sub-filters/views:
    * single answer view: total percent, gender percents, age ranges top5/10, percents top5/10
    * total view: answers top5/10
    * gender view: answers top5/10 for male/female
    * age view: age range selection (possibility of "all") + answers top5/10
    * segment view: segment selection (possibility of "all") + answer top5/10
  * Idea of graphic:
    * BarChart (x = top5/10, y = percent value) of HighChart
    * click on a bar is a link to single answer view