#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <math.h>
#include <hiredis/hiredis.h>
# include <sys/types.h>
# include <sys/timeb.h>

typedef struct {
	float stable_duration;
	float stable_percent;
	float run_percent;
	float rate;
	float meter;
	float max_tempo_drift;
	float max_percent_deviation;
	float max_successive_change;
	float mismatch;
	float loudness;
	float danceability;
	
	unsigned int ID;
	unsigned int year;
	unsigned int digitalID;
	unsigned int artistID;

	unsigned int tag[10];
} Song;

typedef int (*compfn)(const void*, const void*);

#define _DBSIZE_ 357857
#define _RESULT_BUF_SIZE_ 1024000

#define _ART_LIMIT_ 10
#define _TAG_LIMIT_ 10
static Song * songs = NULL;
static Song * buf = NULL;
static char results[_RESULT_BUF_SIZE_] = { '\0' };

static int _s_key = 0;


Song * _load_songs(const char * filename, int max_items, int * items_read){
	Song * songs = (Song*) malloc(sizeof(Song) * max_items);

	FILE * file = fopen(filename, "r");
	int i = 0;
	while(!feof(file)){
		fscanf(file, "%u %f %f %f %f %f %f %f %f %u %u %u %u %u %u %u %u %u %u %u %u %u %f %f %f",
				&songs[i].ID,
				&songs[i].stable_duration,
				&songs[i].stable_percent,
				&songs[i].run_percent,
				&songs[i].rate,
				&songs[i].meter,
				&songs[i].max_tempo_drift,
				&songs[i].max_percent_deviation,
				&songs[i].max_successive_change,

				&songs[i].year,
				&songs[i].digitalID,
				&songs[i].artistID,

				&(songs[i].tag[0]),
				&(songs[i].tag[1]),
				&(songs[i].tag[2]),
				&(songs[i].tag[3]),
				&(songs[i].tag[4]),
				&(songs[i].tag[5]),
				&(songs[i].tag[6]),
				&(songs[i].tag[7]),
				&(songs[i].tag[8]),
				&(songs[i].tag[9]),

				// Added tempo mismatch, Oct 23rd, 2013
				&songs[i].mismatch,
				// Added Jul 30rd, 2014
				&songs[i].loudness,
				&songs[i].danceability
			);
		if(songs[i].ID==0){
		//	printf();
			printf("%d %u %f %u\n",i,songs[i-2].ID,songs[i-2].stable_duration,songs[i-2].year);
		}
		i++;
	}
	*items_read = i;
	fclose(file);

	return songs;
}

void _setup_conditions(const char * args, char * channel, int * key,
		float * cond,
		unsigned int * years,
		unsigned int * artists,
		unsigned int * tags){
	int i = 0; int j = 0;

	sscanf(args, "%s %d %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %u %u %u %u %u %u %u %u %u %u %u %u %u %u %u %u %u %u %u %u %u %u", 
		channel,
		key,
		&cond[0], &cond[1], &cond[2], &cond[3], &cond[4], &cond[5],
		&cond[6], &cond[7], &cond[8], &cond[9], &cond[10], &cond[11],
		&cond[12], &cond[13], &cond[14], &cond[15], &cond[16], &cond[17],
		&cond[18], &cond[19],&cond[20], &cond[21],

		&years[0], &years[1],

		&artists[0], &artists[1], &artists[2], &artists[3], &artists[4], 
		&artists[5], &artists[6], &artists[7], &artists[8], &artists[9],

		&tags[0], &tags[1], &tags[2], &tags[3], &tags[4], 
		&tags[5], &tags[6], &tags[7], &tags[8], &tags[9]
	);


	printf("%s\n", channel);
	for(i = 0; i < 4; i++){
		for(j = 0; j < 4; j++){
			printf("\t%f", cond[i*4+j]);
		}
		printf("\n");
	}
	printf("Year: %u ~ %u\n", years[0], years[1]);
	for(i = 0; i < 10; i++){
		printf("%u\t", artists[i]);
	}
	printf("\n");
	for(i = 0; i < 10; i++){
		printf("%u\t", tags[i]);
	}
	printf("\n");
	printf(" -- condition done -- \n");
}
void _print_duration(char * str, clock_t ts, clock_t te){
	printf("%s -> %.8f seconds\n", str, (te-ts)/1000000.0);
}

int _search(Song * songs, int N,  // `songs` is the DB, and N is the number of songs in DB
		const float * cond,   // 16 floats representing the bounds for 8 fields
		unsigned int * year,  // 2 uint, year[0] is start, year[1] is end
		unsigned int * artists, 
		unsigned int * tags,
		Song * res  // result buffer
){
	int i = 0; int count = 0;
	for(i = 0; i < N; i++){

		/* Artist check */
		int j = 0; int good = 0;
		for(j = 0; j < _ART_LIMIT_; j++){
			if(artists[j] == 0){
				good = (j == 0);
				break;
			}
			if(songs[i].artistID == artists[j]){
				good = 1;
				break;
			}
		}
		// Artist not match, skip this record
		if(!good){
			continue;
		}

		/* Tag check */
		good = 0;
		for(j = 0; j < _TAG_LIMIT_; j++){
			if(tags[j] == 0){
				good = (j == 0);
				break;
			}
			int k = 0;
			for(k = 0; k < 10; k++){
				if(songs[i].tag[k] == tags[j]){
					good = 1;
					goto TAG_CHECK_DONE;
				}
			}
		}
TAG_CHECK_DONE:
		// No tag matches, skip this record
		if(!good){
			continue;
		}

		good = 1;
		/* Filter check */
		if(songs[i].year < year[0] || songs[i].year > year[1]
			|| songs[i].stable_duration < cond[0] || songs[i].stable_duration > cond[1]
			|| songs[i].stable_percent < cond[2] || songs[i].stable_percent > cond[3]
			|| songs[i].run_percent < cond[4] || songs[i].run_percent > cond[5]
			|| songs[i].rate < cond[6] || songs[i].rate > cond[7]
			|| songs[i].meter < cond[8] || songs[i].meter > cond[9]
			|| songs[i].max_tempo_drift < cond[10] || songs[i].max_tempo_drift > cond[11]
			|| songs[i].max_percent_deviation < cond[12] || songs[i].max_percent_deviation > cond[13]
			|| songs[i].max_successive_change < cond[14] || songs[i].max_successive_change > cond[15]
			|| songs[i].mismatch < cond[16] || songs[i].mismatch > cond[17]
			|| songs[i].loudness < cond[18] || songs[i].loudness > cond[19]
			|| songs[i].danceability < cond[20] || songs[i].danceability > cond[21]
			){
			good = 0;
		}

		if(!good){
			continue;
		}

		res[count] = songs[i];
		count += 1;
	}

	return count;
}

#define _GETFLOAT(song, key)  *(  (float*)( ((char*)(song)) + ((key)*sizeof(float)) ) )

float _get_float(Song * song, int key){
	char * v = (char*) song;

	return *( (float*)( v+(key * sizeof(float)) ) );
}
void _prepare_results(Song * songs, int N, int key, int M, char * result, int bufsize){
	float min, max, bin;
	int * bins = (int*)malloc(M * sizeof(int));

	int len = snprintf(result, bufsize, "%d$", key);

	int i = 0;
	for(i = 0; i < M; i++) bins[i] = 0;

	min = _GETFLOAT( &songs[0], key );
	max = _GETFLOAT( &songs[N-1], key );

	bin = (max-min) / M;

	/* Hist */
	int curbin = 0;
	float v = 0;
	for(i = 0; i < N; i++){
		v = _GETFLOAT(&songs[i], key );

		if(v <= (curbin * bin + bin +min)){
			bins[curbin] += 1;
		}else{
			curbin++;
			i--;
			continue;
		}
	}

	len += snprintf(result+len, bufsize-len, "%f,%f,%f$", min, max, bin);

	for(i = 0; i < M; i++){
		len += snprintf(result+len, bufsize - len, "%f,%d,", (i*bin)+min, bins[i]);
	}

	free(bins);

	len += snprintf(result+len, bufsize - len, "$");

	/* Points */

	int step = 1;
	if(N > 1000){
		step = N/1000;
	}
	
	unsigned int seedVal;
	struct_timeb timeBuf;
	_ftime (&timeBuf);
	seedVal = ( ( ( ( (unsigned int)timeBuf, time & 0xFFFF) +
                   (unsigned int)timeBuf, millitm) ^
                   (unsigned int)timeBuf, millitm) ;
	srand ((unsigned int)seedVal);
	int r, range, r_min, r_max;
	double j;
	r_min=0;
	r_max=step - 1;
	range=r_max-r_min;
	r=rand();
	j=((double)r/(double)RAND_MAX);
	r=(int)(j * (double)range);

	for(i = r; i < N; i+=step){
		len += snprintf(result+len, bufsize - len, "%d,%f,", songs[i].ID, _GETFLOAT( &songs[i], key ));
	}

	if( (i-step) < N-1){
		len += snprintf(result+len, bufsize - len, "%d,%f,", songs[N-1].ID, _GETFLOAT( &songs[N-1], key ));
	}

	/* Portion */

	len += snprintf(result+len, bufsize - len, "$%d,%d", _DBSIZE_, N);

}

int compare(Song * a, Song * b){

	float x = _GETFLOAT(a, _s_key);
	float y = _GETFLOAT(b, _s_key);

	double sub = x - y;
	double abs = fabs(sub);

	if(abs < 0.00001){ return 0; }
	if(sub < 0){ return -1; }
	else{ return 1; }
}

int main(int argc, char** argv)
{
	float        cond[22] = {0};
	unsigned int years[2] = {0};
	unsigned int arts[_ART_LIMIT_] = {0};
	unsigned int tags[_TAG_LIMIT_] = {0};

	int items_loaded = 0;

	clock_t ts, te;

	buf = (Song*) malloc(100000000 * sizeof(Song));

	printf("Start loading...\n");
	ts = clock();
	songs = _load_songs("nsongs.final.db", _DBSIZE_, &items_loaded);
	te = clock();
	_print_duration("Load songs", ts, te);
	printf("Loading finished... %d records loaded. %d memory allocated\n", items_loaded, _DBSIZE_ * sizeof(Song));


	redisContext *context = redisConnect("127.0.0.1", 6379);
	if (context != NULL && context->err) {
		printf("Error: %s\n", context->errstr);
		return -1;
	}

	redisReply * reply = NULL;

	char channel[128] = { '\0' };
	char command[1024] = { '\0' };

	int j = 0;
	printf("Waiting for reply...\n");
	while( NULL != (reply = redisCommand(context, "BRPOP musicrx 1")) ){
		printf("Get reply. Type %d\n", reply->type);
		switch(reply->type){
			case REDIS_REPLY_STATUS:
				printf("%s\n", reply->str);
				break;
			case REDIS_REPLY_ERROR:
				printf("%s\n", reply->str);
				break;
			case REDIS_REPLY_INTEGER:
				printf("%lld\n", reply->integer);
				break;
			case REDIS_REPLY_NIL:
				printf("nil received\n");
				break;
			case REDIS_REPLY_ARRAY:
				printf("array received\n");

				printf(" -> %s\n", reply->element[1]->str);

				_setup_conditions(reply->element[1]->str, channel, &_s_key,
						cond, years, arts, tags);
				ts = clock();
				int number = _search(songs, _DBSIZE_, cond, years, arts, tags, buf);
				te = clock();
				_print_duration("Search songs", ts, te);
				printf("\tResult count : %d\n", number);

				ts = clock();
				qsort((void*) buf, number,
						sizeof(Song),
						(compfn)compare);
				te = clock();
				_print_duration("Sort", ts, te);

				_prepare_results(buf, number, _s_key, 30, results, _RESULT_BUF_SIZE_);

				redisCommand(context, "PUBLISH %s %s", channel, results);

				break;
			case REDIS_REPLY_STRING:
				printf("%s\n", reply->str);
				break;
			default:
				break;
		}
		freeReplyObject(reply);
	}


	return 0;
}
