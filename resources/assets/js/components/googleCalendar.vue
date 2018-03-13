<style scoped>

</style>

<template>

    <div >

        <datepicker style="width:100%;" :value="currentDate" v-on:selected="dateChosen" :inline="true" calendar-class="vue__datepicker" ></datepicker>

        <div style="margin-top: .5em; margin-bottom: .5em;"> 
            <form id="new_event" class="form-inline my-2 my-lg-0" v-on:submit.prevent="addNewEvent">
                <div class="form-group">
                    <input type="text" class="form-control" id="new_event_name" name="new_event_name" placeholder="Add a event" v-model="new_event_name" >
                </div>
                <button type="submit" class="btn btn-default">Submit</button>
            </form>
        </div>
        
        <div>
            <p v-for="event in calendar.data.items" :key="event.id"> {{ event.summary }} <br /> 
            <span class="small">{{ formatDate(event.start) }} - {{ formatDate(event.end) }}</span></p>
        </div>

    </div>

</template>


<script>
    import Datepicker from 'vuejs-datepicker';
    import moment from 'moment';
    export default {
        components: {
            Datepicker
        },
        mixins: [],
        data () {
            return {
                calendarId: '',
                calendar: {"data": {"items": []} },
                currentDate: '',
                startDate: '',
                endDate: '',
                new_event_name: '',
                theme: null
            }
        },
        created() {
            this.setCalendar();
            this.theme = this.coldreader;
        },
        props: [
            'aspectId',
            'aspectType',
            'aspectNotes',
            'aspectData',
            'aspectSource',
            'hidden',
            'subjectId',
            'settingsCalendarId',
            'title'
        ],
        computed: {
            
        },
        methods: {
            setCalendar(){
                console.log('calendar debug: '+ this.$coldreaderTheme() );
                this.calendarId = this.settingsCalendarId;
                this.currentDate = new Date();
                this.dateChosen(this.currentDate);
            },
            formatDate(googleDate){
                // This is a weird thing, because google uses strange date objects in their JSON.
                var dateObject;
                if ( googleDate.dateTime !=null ){
                    dateObject = new Date(googleDate.dateTime);
                } else {
                    dateObject = new Date(googleDate.date);
                }
                return moment(dateObject).format('llll');
            },
            fetchCalendar(){
                var self=this;
                var fd = new Object();
                fd.start_date = this.startDate;
                fd.end_date = this.endDate;
                fd.calendar_id = this.calendarId;
                this.$axios.get('/gcal/calendar', {params: fd})
                    .then(function(response){
                        self.calendar = response;
                        console.log(response);
                        self.$nextTick(function(){
                            self.$rejigger('from google calendar');
                        });
                    })
                    .catch(function(error){
                        console.log(error);
                    });
                
            },
            createCalendar(){
                //
            },
            dateChosen(date_object){
                console.log(date_object);
                this.startDate = new Date(date_object);
                this.startDate.setHours(0);
                this.startDate.setMinutes(0);
                this.startDate.setSeconds(1);
                this.endDate = new Date( this.startDate.toDateString() );
                this.endDate.setHours(23);
                this.endDate.setMinutes(59);
                this.endDate.setSeconds(59);
                this.fetchCalendar();
            },
            addNewEvent(){
                var self = this;
                console.log('Adding new event!');
                var fd = new Object();
                fd.calendar_id = this.calendarId;
                fd.new_event_name = this.new_event_name;
                var fd_string = JSON.stringify(fd);
                this.$axios.post('/gcal/event/create', fd_string)
                    .then(function(response){
                        self.new_event_name = ''; 
                        self.fetchCalendar();
                    })
                    .catch(function(error){
                        console.log(error);
                    });
            }
        }
    };

</script>