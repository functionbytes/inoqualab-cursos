



<div class="owl-nav">


    <form action="{{ route('customers.courses.prev') }}" method="POST">
    
        {{ csrf_field() }}
        
            <input type="hidden" name="course" value="{{ $course->id }}">
            <input type="hidden"name="user" value="{{ $user->id }}">
                    

            <button type="submit" role="presentation" class="owl">
                <i class="fas fa-chevron-left"></i>
            </button>
                

    </form>


    
<form action="{{ route('customers.courses.realized') }}" method="POST">
    
        {{ csrf_field() }}
    
        <input type="hidden" name="course" value="{{ $course->id }}">
        <input type="hidden"name="user" value="{{ $user->id }}">

            <button type="submit" role="presentation" class="owl">
                <i class="fas fa-chevron-right"></i>
            </button>
            

    </form>



</div>



    



